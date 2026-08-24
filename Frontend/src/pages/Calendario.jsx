import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { get } from '../services/api.js'

export default function Calendario(){
  const {id}=useParams(); const torneoId=id|| new URLSearchParams(window.location.search).get('torneoId')||1
  const [cards,setCards]=useState([]); const [page,setPage]=useState(1); const limit=10
  useEffect(()=>{ get(`/torneos/${torneoId}/calendario?page=${page}&limit=${limit}`).then(r=>{
    // agrupa por jornada
    const grouped={}; r.data.forEach(p=>{ const k=`Jornada ${p.jornada_nro||p.jornada_id}`; (grouped[k]=grouped[k]||[]).push(p) })
    setCards(Object.entries(grouped))
  }).catch(()=>{}) },[torneoId,page])
  return (
    <div>
      <h3>Calendario {torneoId && `- Torneo ${torneoId}`}</h3>
      {cards.length===0 ? <div className="alert alert-info">Sin partidos</div> : cards.map(([jornada, partidos])=>(
        <div key={jornada} className="card mb-3"><div className="card-header tw-font-bold">{jornada}</div><div className="card-body row">
          {partidos.map(p=>(
            <div key={p.id} className="col-md-6 mb-2"><div className="border rounded p-2">
              <div>{p.equipoA_id} vs {p.equipoB_id} <small className="text-muted">{p.fechaHora}</small></div>
              <span className={`badge ${p.estado==='finalizado'?'bg-success':'bg-warning'}`}>{p.estado}</span>
              <a href={`/partidos/${p.id}`} className="btn btn-sm btn-outline-primary ms-2">Goles/Faltas/Tarjetas</a>
            </div></div>
          ))}
        </div></div>
      ))}
      <button className="btn btn-sm btn-outline-secondary" onClick={()=>setPage(p=>Math.max(1,p-1))}>Anterior</button>
      <button className="btn btn-sm btn-outline-secondary ms-1" onClick={()=>setPage(p=>p+1)}>Siguiente</button>
    </div>
  )
}
