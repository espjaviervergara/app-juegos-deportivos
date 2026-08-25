import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { get, put } from '../services/api.js'

export default function Calendario(){
  const {id}=useParams(); const torneoId=id|| new URLSearchParams(window.location.search).get('torneoId')||1
  const [cards,setCards]=useState([]); const [sinAsignar,setSinAsignar]=useState([]); const [jornadas,setJornadas]=useState([]); const [page,setPage]=useState(1); const limit=10
  const [msg,setMsg]=useState('')

  async function load(){
    const r=await get(`/torneos/${torneoId}/calendario?page=${page}&limit=${limit}`).catch(()=>({data:[]}))
    const grouped={}; r.data.forEach(p=>{ const k=`Jornada ${p.jornada_nro||p.jornada_id}` + (p.grupo_nombre?` - ${p.grupo_nombre}`:''); (grouped[k]=grouped[k]||[]).push(p) })
    setCards(Object.entries(grouped))
    const s=await get(`/torneos/${torneoId}/partidos/sin-asignar`).catch(()=>({data:[]})); setSinAsignar(s.data)
    const j=await get(`/torneos/${torneoId}/jornadas`).catch(()=>({data:[]})); setJornadas(j.data)
  }
  useEffect(()=>{ load() },[torneoId,page])

  async function reasignar(partidoId, jornadaId, fechaHora){
    try{
      await put(`/partidos/${partidoId}`, {jornadaId: jornadaId?parseInt(jornadaId):null, fechaHora})
      setMsg('Reasignado'); load()
    }catch(e){ setMsg(e.message) }
  }

  return (
    <div>
      <h3>Calendario {torneoId && `- Torneo ${torneoId}`}</h3>
      {msg && <div className="alert alert-info">{msg}</div>}
      {cards.length===0 ? <div className="alert alert-info">Sin partidos en calendario</div> : cards.map(([jornada, partidos])=>(
        <div key={jornada} className="card mb-3"><div className="card-header tw-font-bold">{jornada}</div><div className="card-body row">
          {partidos.map(p=>(
            <div key={p.id} className="col-md-6 mb-2"><div className="border rounded p-2">
              <div>{p.equipoA_nombre || p.equipoA_id} vs {p.equipoB_nombre || p.equipoB_id} <small className="text-muted">{p.fechaHora}</small> {p.grupo_nombre && <span className="badge bg-info ms-1">{p.grupo_nombre}</span>}</div>
              <span className={`badge ${p.estado==='finalizado'?'bg-success':'bg-warning'}`}>{p.estado}</span>
              <a href={`/partidos/${p.id}`} className="btn btn-sm btn-outline-primary ms-1">Goles/Faltas/Tarjetas</a>
              <Reasignar partido={p} jornadas={jornadas} onReasignar={reasignar} />
            </div></div>
          ))}
        </div></div>
      ))}
      {sinAsignar.length>0 && (
        <div className="card border-warning mb-3">
          <div className="card-header bg-warning">Sin asignar ({sinAsignar.length}) — partidos generados sin jornada</div>
          <div className="card-body row">
            {sinAsignar.map(p=>(
              <div key={p.id} className="col-md-6 mb-2"><div className="border rounded p-2">
                <div>{p.equipoA_nombre} vs {p.equipoB_nombre} <small className="text-muted">{p.grupo_nombre||'Sin grupo'}</small></div>
                <Reasignar partido={p} jornadas={jornadas} onReasignar={reasignar} />
                <a href={`/partidos/${p.id}`} className="btn btn-sm btn-outline-primary ms-1">Ver</a>
              </div></div>
            ))}
          </div>
        </div>
      )}
      <button className="btn btn-sm btn-outline-secondary" onClick={()=>setPage(p=>Math.max(1,p-1))}>Anterior</button>
      <button className="btn btn-sm btn-outline-secondary ms-1" onClick={()=>setPage(p=>p+1)}>Siguiente</button>
    </div>
  )
}

function Reasignar({partido, jornadas, onReasignar}){
  const [jornada,setJornada]=useState(partido.jornada_id||''); const [fecha,setFecha]=useState(partido.fechaHora?partido.fechaHora.slice(0,16):'')
  return (
    <div className="d-flex gap-1 mt-1">
      <select className="form-select form-select-sm w-auto" value={jornada} onChange={e=>setJornada(e.target.value)}>
        <option value="">Sin asignar</option>
        {jornadas.map(j=> <option key={j.id} value={j.id}>Jornada {j.nro}</option>)}
      </select>
      <input type="datetime-local" className="form-control form-control-sm w-auto" value={fecha} onChange={e=>setFecha(e.target.value)} />
      <button className="btn btn-sm btn-outline-secondary" onClick={()=> onReasignar(partido.id, jornada, fecha?fecha.replace('T',' ')+':00':null)}>Reasignar</button>
    </div>
  )
}
