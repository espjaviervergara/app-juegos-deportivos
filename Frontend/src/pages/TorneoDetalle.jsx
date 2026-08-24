import { useParams, Link } from 'react-router-dom'
import { useEffect, useState } from 'react'
import { get } from '../services/api.js'

export default function TorneoDetalle(){
  const {id}=useParams(); const [t,setT]=useState(null); const [tab,setTab]=useState('equipos')
  useEffect(()=>{ get(`/torneos/${id}`).then(r=>setT(r.data)).catch(()=>{}) },[id])
  if(!t) return <div>Cargando...</div>
  return (
    <div>
      <h3>{t.nombre} <small className="text-muted">{t.categoria} {t.formato}</small></h3>
      <ul className="nav nav-tabs mb-3">
        {['equipos','jornadas','calendario','clasificacion'].map(k=><li key={k} className="nav-item"><button className={`nav-link ${tab===k?'active':''}`} onClick={()=>setTab(k)}>{k}</button></li>)}
      </ul>
      {tab==='equipos' && <EquiposTab id={id}/>}
      {tab==='calendario' && <div><Link to={`/torneos/${id}/calendario`}>Ver calendario completo</Link></div>}
      {tab==='clasificacion' && <Link to={`/torneos/${id}/clasificacion`}>Ver tabla</Link>}
    </div>
  )
}
function EquiposTab({id}){ const [rows,setRows]=useState([]); useEffect(()=>{ get(`/torneos/${id}/equipos`).then(r=>setRows(r.data)).catch(()=>{}) },[id]); return <ul className="list-group">{rows.map(e=><li key={e.id} className="list-group-item">{e.nombre}</li>)}</ul> }
