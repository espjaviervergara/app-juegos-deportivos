import { useEffect, useState } from 'react'
import { get, post, del } from '../services/api.js'

const endpoints = {
  deportes: { list:'/deportes', create:'/deportes', fields:['nombre'] },
  torneos: { list:'/torneos', create:'/torneos', fields:['nombre','deporteId','categoria','formato'] },
  equipos: { list:'/equipos', create:'/equipos', fields:['nombre'] },
}

export default function Gestion({tipo}){
  const [rows,setRows]=useState([]); const [form,setForm]=useState({}); const [msg,setMsg]=useState(''); const [page,setPage]=useState(1)
  const [deportes,setDeportes]=useState([])
  const cfg = endpoints[tipo] || endpoints.deportes

  async function load(){ try{ const r=await get(`${cfg.list}?page=${page}&limit=10`); setRows(r.data)}catch(e){ setMsg(e.message)} }
  useEffect(()=>{ load() },[page,tipo])
  useEffect(()=>{
    if(tipo==='torneos'){
      get('/deportes').then(r=> setDeportes(r.data)).catch(()=>{})
      setForm(f=> ({categoria: f.categoria||'M', formato: f.formato||'liga', deporteId: f.deporteId||'', ...f}))
    }
  },[tipo])

  async function crear(e){
    e.preventDefault();
    try{
      const body={}; cfg.fields.forEach(f=> body[f]=form[f])
      if(tipo==='torneos'){ body.deporteId=parseInt(body.deporteId); if(!body.deporteId) throw Object.assign(new Error('Selecciona deporte'),{code:'VALIDATION_ERROR'}) }
      await post(cfg.create, body); setForm(tipo==='torneos'?{categoria:'M',formato:'liga',deporteId:''}:{}); await load(); setMsg('Creado')
    }catch(er){ setMsg(er.code==='CONFLICT'?'Ya existe': er.details ? JSON.stringify(er.details) : er.message) }
  }

  return (
    <div>
      <h4>Gestión {tipo}</h4>
      {msg && <div className="alert alert-info">{msg}</div>}
      <form onSubmit={crear} className="d-flex gap-2 mb-3 flex-wrap align-items-end">
        {cfg.fields.map(f=>{
          if(tipo==='torneos' && f==='deporteId'){
            return (
              <div key={f}>
                <label className="form-label small mb-1">Deporte</label>
                <select className="form-select" value={form[f]||''} onChange={e=>setForm({...form,[f]:e.target.value})} required>
                  <option value="">-- Selecciona deporte --</option>
                  {deportes.map(d=> <option key={d.id} value={d.id}>{d.nombre}</option>)}
                </select>
              </div>
            )
          }
          if(tipo==='torneos' && f==='categoria'){
            return (
              <div key={f}>
                <label className="form-label small mb-1">Categoría</label>
                <select className="form-select" value={form[f]||'M'} onChange={e=>setForm({...form,[f]:e.target.value})}>
                  <option value="M">Masculino (M)</option>
                  <option value="F">Femenino (F)</option>
                  <option value="Mixto">Mixto</option>
                </select>
              </div>
            )
          }
          if(tipo==='torneos' && f==='formato'){
            return (
              <div key={f}>
                <label className="form-label small mb-1">Formato</label>
                <select className="form-select" value={form[f]||'liga'} onChange={e=>setForm({...form,[f]:e.target.value})}>
                  <option value="liga">Liga</option>
                  <option value="eliminatoria">Eliminatoria</option>
                  <option value="grupos+eliminatoria">Grupos + Eliminatoria</option>
                </select>
              </div>
            )
          }
          // nombre propio: input manual
          return (
            <div key={f}>
              <label className="form-label small mb-1">{f}</label>
              <input className="form-control" value={form[f]||''} onChange={e=>setForm({...form,[f]:e.target.value})} placeholder={f==='nombre'?'Nombre propio':f} required={f==='nombre'} />
            </div>
          )
        })}
        <button className="btn btn-primary mt-3">Crear</button>
      </form>
      <ul className="list-group">
        {rows.map(r=>(
          <li key={r.id} className="list-group-item d-flex justify-content-between align-items-center">
            <span>{r.nombre||r.id} <small className="text-muted">#{r.id} {r.categoria?`[${r.categoria}]`:''} {r.formato?`(${r.formato})`:''}</small></span>
            <button className="btn btn-sm btn-outline-danger" onClick={async()=>{ await del(`${cfg.list}/${r.id}`).catch(e=>setMsg(e.message)); load()}}>Borrar</button>
          </li>
        ))}
      </ul>
      <div className="mt-2">
        <button className="btn btn-sm btn-outline-secondary me-1" onClick={()=>setPage(p=>Math.max(1,p-1))}>Anterior</button>
        <button className="btn btn-sm btn-outline-secondary" onClick={()=>setPage(p=>p+1)}>Siguiente</button>
      </div>
      <div className="alert alert-light mt-3">
        <strong>Regla:</strong> Solo nombres propios (equipo, jugador) se escriben. Todo lo que viene de BD (deporte, categoría, formato) se elige con <code>select</code>.
      </div>
    </div>
  )
}
