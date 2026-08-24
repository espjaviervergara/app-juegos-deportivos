import { useEffect, useState } from 'react'
import { get, post, del } from '../services/api.js'

export default function Gestion({tipo}){
  const [rows,setRows]=useState([]); const [nombre,setNombre]=useState(''); const [msg,setMsg]=useState('')
  const endpoint = tipo==='deportes' ? '/deportes' : '/deportes'
  useEffect(()=>{ get(endpoint).then(r=>setRows(r.data)).catch(e=>setMsg(e.message)) },[endpoint])
  async function crear(e){ e.preventDefault(); try{ await post(endpoint,{nombre}); setNombre(''); const r=await get(endpoint); setRows(r.data); setMsg('Creado') }catch(er){ setMsg(er.code==='CONFLICT'?'Ya existe':er.message) } }
  return (
    <div>
      <h4>Gestión {tipo}</h4>
      {msg && <div className="alert alert-info">{msg}</div>}
      <form onSubmit={crear} className="d-flex gap-2 mb-3"><input className="form-control w-auto" value={nombre} onChange={e=>setNombre(e.target.value)} placeholder="nombre"/><button className="btn btn-primary">Crear</button></form>
      <ul className="list-group">{rows.map(r=><li key={r.id} className="list-group-item d-flex justify-content-between">{r.nombre}<button className="btn btn-sm btn-outline-danger" onClick={async()=>{ await del(`${endpoint}/${r.id}`).catch(e=>setMsg(e.message)); const res=await get(endpoint); setRows(res.data)}}>Borrar</button></li>)}</ul>
      <small className="text-muted">409 duplicado, 422 validación, 403 solo admin</small>
    </div>
  )
}
