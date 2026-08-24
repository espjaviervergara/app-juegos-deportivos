import { useEffect, useState } from 'react'
import { get, post, del, put } from '../services/api.js'

const endpoints = {
  deportes: { list:'/deportes', create:'/deportes', fields:['nombre'] },
  torneos: { list:'/torneos', create:'/torneos', fields:['nombre','deporteId','categoria','formato'] },
  equipos: { list:'/equipos', create:'/equipos', fields:['nombre'] },
}

export default function Gestion({tipo}){
  const [rows,setRows]=useState([]); const [form,setForm]=useState({}); const [msg,setMsg]=useState(''); const [page,setPage]=useState(1)
  const cfg = endpoints[tipo] || endpoints.deportes
  async function load(){ try{ const r=await get(`${cfg.list}?page=${page}&limit=10`); setRows(r.data)}catch(e){ setMsg(e.message)} }
  useEffect(()=>{ load() },[page,tipo])
  async function crear(e){
    e.preventDefault();
    try{
      const body={}; cfg.fields.forEach(f=> body[f]=form[f])
      if(tipo==='torneos'){ body.deporteId=parseInt(body.deporteId); }
      await post(cfg.create, body); setForm({}); await load(); setMsg('Creado')
    }catch(er){ setMsg(er.code==='CONFLICT'?'Ya existe': er.details ? JSON.stringify(er.details) : er.message) }
  }
  return (
    <div>
      <h4>Gestión {tipo}</h4>
      {msg && <div className="alert alert-info">{msg}</div>}
      <form onSubmit={crear} className="d-flex gap-2 mb-3 flex-wrap">
        {cfg.fields.map(f=>(
          <input key={f} className="form-control w-auto" value={form[f]||''} onChange={e=>setForm({...form,[f]:e.target.value})} placeholder={f} required={f==='nombre'} />
        ))}
        <button className="btn btn-primary">Crear</button>
      </form>
      {tipo==='torneos' && <small className="text-muted d-block mb-2">categoria: M/F/Mixto, formato: liga/eliminatoria/grupos+eliminatoria, deporteId: 1=Fútbol 2=Básquet 3=Vóley</small>}
      <ul className="list-group">
        {rows.map(r=>(
          <li key={r.id} className="list-group-item d-flex justify-content-between align-items-center">
            <span>{r.nombre||r.id} <small className="text-muted">#{r.id}</small></span>
            <button className="btn btn-sm btn-outline-danger" onClick={async()=>{ await del(`${cfg.list}/${r.id}`).catch(e=>setMsg(e.message)); load()}}>Borrar</button>
          </li>
        ))}
      </ul>
      <div className="mt-2">
        <button className="btn btn-sm btn-outline-secondary me-1" onClick={()=>setPage(p=>Math.max(1,p-1))}>Anterior</button>
        <button className="btn btn-sm btn-outline-secondary" onClick={()=>setPage(p=>p+1)}>Siguiente</button>
      </div>
      <div className="alert alert-light mt-3">
        <strong>Flujo:</strong> Torneos → Equipos (crear) → Torneos/{'{id}'}/equipos attach → Jornadas → Partidos (valida solapamiento) → Resultados (editor propone, admin aprueba).
        <br/>Usa Postman/curl para jornadas/partidos/resultados hasta completar UI específica.
      </div>
    </div>
  )
}
