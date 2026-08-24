import { useEffect, useState } from 'react'
import { get } from '../services/api.js'

export default function Auditoria(){
  const [rows,setRows]=useState([]); const [page,setPage]=useState(1); const limit=20
  useEffect(()=>{ get(`/auditoria?page=${page}&limit=${limit}`).then(r=>setRows(r.data)).catch(()=>{}) },[page])
  return (
    <div>
      <h4>Auditoría (solo admin)</h4>
      <table className="table table-sm"><thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Torneo</th></tr></thead><tbody>{rows.map(a=><tr key={a.id}><td>{a.created_at}</td><td>{a.usuario_id}</td><td>{a.accion}</td><td>{a.torneo_id}</td></tr>)}</tbody></table>
      <button className="btn btn-sm btn-outline-secondary" onClick={()=>setPage(p=>Math.max(1,p-1))}>Anterior</button><button className="btn btn-sm btn-outline-secondary ms-1" onClick={()=>setPage(p=>p+1)}>Siguiente</button>
    </div>
  )
}
