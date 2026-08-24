const BASE = import.meta.env.VITE_API_BASE || '/api/v1'

let accessToken = sessionStorage.getItem('accessToken') || null
let refreshPromise = null

export function setAccessToken(t){ accessToken=t; if(t) sessionStorage.setItem('accessToken',t); else sessionStorage.removeItem('accessToken') }
export function getAccessToken(){ return accessToken }

async function refresh(){
  if(refreshPromise) return refreshPromise
  refreshPromise = fetch(`${BASE}/auth/refresh`, { method:'POST', credentials:'include' })
    .then(async r=>{
      if(!r.ok) throw new Error('refresh failed')
      const j=await r.json(); setAccessToken(j.data.accessToken); return j.data.accessToken
    }).finally(()=> refreshPromise=null)
  return refreshPromise
}

export async function api(path, opts={}){
  const url = `${BASE}${path}`
  const headers = { 'Content-Type':'application/json', ...(opts.headers||{}) }
  if(accessToken) headers['Authorization']=`Bearer ${accessToken}`
  if(opts.method && ['POST','PUT','DELETE'].includes(opts.method.toUpperCase())){
    const ov = headers['X-HTTP-Method-Override']; if(!ov) {} // keep
  }
  let res = await fetch(url, { ...opts, headers, credentials:'include', body: opts.body?JSON.stringify(opts.body):undefined })
  if(res.status===401 && !opts._retry){
    try{ await refresh(); headers['Authorization']=`Bearer ${accessToken}`; res = await fetch(url, { ...opts, headers, credentials:'include', body: opts.body?JSON.stringify(opts.body):undefined, _retry:true }) }catch(e){ /* fallthrough */ }
  }
  const retryAfter = res.headers.get('Retry-After')
  if(res.status===429) throw Object.assign(new Error('Rate limited'), { status:429, retryAfter })
  const data = await res.json().catch(()=> ({}))
  if(!res.ok){
    const err = new Error(data.error?.message || 'Error')
    err.status=res.status; err.code=data.error?.code; err.details=data.error?.details
    throw err
  }
  return data
}
export const get = (p,q='')=> api(`${p}${q}`)
export const post = (p,b)=> api(p,{method:'POST',body:b})
export const put = (p,b)=> api(p,{method:'PUT',body:b})
export const del = (p)=> api(p,{method:'DELETE'})
