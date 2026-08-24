import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { get } from '../services/api.js'

export default function Clasificacion(){
  const {id}=useParams(); const [rows,setRows]=useState([]); const [page,setPage]=useState(1); const limit=20
  useEffect(()=>{ if(!id) return; get(`/torneos/${id}/clasificaciones?page=${page}&limit=${limit}`).then(r=>setRows(r.data)).catch(()=>{}) },[id,page])
  return (
    <div>
      <h3>Clasificación Torneo {id}</h3>
      <table className="table table-striped table-bordered">
        <thead><tr><th>#</th><th>Equipo</th><th>PJ</th><th>PG</th><th>PE</th><th>PP</th><th>GF</th><th>GC</th><th>GA</th><th>Pts</th></tr></thead>
        <tbody>{rows.map((r,i)=><tr key={r.equipo_id}><td>{(page-1)*limit+i+1}</td><td>{r.equipo}</td><td>{r.PJ}</td><td>{r.PG}</td><td>{r.PE}</td><td>{r.PP}</td><td>{r.GF}</td><td>{r.GC}</td><td>{r.GA}</td><td className="tw-font-bold">{r.puntos}</td></tr>)}</tbody>
      </table>
      <button className="btn btn-sm btn-outline-secondary" onClick={()=>setPage(p=>Math.max(1,p-1))}>Anterior</button>
      <button className="btn btn-sm btn-outline-secondary ms-1" onClick={()=>setPage(p=>p+1)}>Siguiente</button>
    </div>
  )
}
