import { useParams, Link } from 'react-router-dom'
import { useEffect, useState } from 'react'
import { get, post, del } from '../services/api.js'

export default function TorneoDetalle(){
  const {id}=useParams(); const [t,setT]=useState(null); const [tab,setTab]=useState('equipos')
  useEffect(()=>{ get(`/torneos/${id}`).then(r=>setT(r.data)).catch(()=>{}) },[id])
  if(!t) return <div>Cargando...</div>
  return (
    <div>
      <h3>{t.nombre} <small className="text-muted">{t.categoria} {t.formato}</small></h3>
      <ul className="nav nav-tabs mb-3">
        {['equipos','grupos','jornadas','calendario','clasificacion'].map(k=><li key={k} className="nav-item"><button className={`nav-link ${tab===k?'active':''}`} onClick={()=>setTab(k)}>{k}</button></li>)}
      </ul>
      {tab==='equipos' && <EquiposTab id={id}/>}
      {tab==='grupos' && <GruposTab torneoId={id}/>}
      {tab==='jornadas' && <JornadasTab torneoId={id}/>}
      {tab==='calendario' && <div><Link to={`/torneos/${id}/calendario`}>Ver calendario completo (cards por jornada → grupo)</Link></div>}
      {tab==='clasificacion' && <Link to={`/torneos/${id}/clasificacion`}>Ver tabla clásica</Link>}
    </div>
  )
}
function EquiposTab({id}){ const [rows,setRows]=useState([]); useEffect(()=>{ get(`/torneos/${id}/equipos`).then(r=>setRows(r.data)).catch(()=>{}) },[id]); return <ul className="list-group">{rows.map(e=><li key={e.id} className="list-group-item d-flex justify-content-between"><span>{e.nombre} <small className="text-muted">#{e.id}</small></span><a href={`/equipos/${e.id}`} className="btn btn-sm btn-outline-primary">Ver / Añadir jugadores</a></li>)}</ul> }

function GruposTab({torneoId}){
  const [grupos,setGrupos]=useState([]); const [nombre,setNombre]=useState(''); const [numGrupos,setNumGrupos]=useState(2); const [msg,setMsg]=useState('')
  const [equipos,setEquipos]=useState([]); const [selEquipo,setSelEquipo]=useState('')
  async function load(){ const r=await get(`/torneos/${torneoId}/grupos`).catch(()=>({data:[]})); setGrupos(r.data); const e=await get(`/torneos/${torneoId}/equipos`).catch(()=>({data:[]})); setEquipos(e.data) }
  useEffect(()=>{ load() },[torneoId])
  async function crearManual(e){ e.preventDefault(); try{ await post(`/torneos/${torneoId}/grupos`,{nombre}); setNombre(''); load() }catch(er){ setMsg(er.message)} }
  async function crearAuto(){ try{ await post(`/torneos/${torneoId}/grupos/auto`,{numGrupos:parseInt(numGrupos), replace:false}); load() }catch(er){ setMsg(er.message) } }
  async function addEquipo(grupoId){
    if(!selEquipo) return setMsg('Selecciona equipo')
    try{ await post(`/grupos/${grupoId}/equipos`,{equipoId:parseInt(selEquipo)}); load() }catch(er){ setMsg(er.message) }
  }
  return (
    <div>
      {msg && <div className="alert alert-info">{msg}</div>}
      <div className="d-flex gap-2 mb-3">
        <form onSubmit={crearManual} className="d-flex gap-1">
          <input className="form-control" value={nombre} onChange={e=>setNombre(e.target.value)} placeholder="Nombre Grupo (A)" required />
          <button className="btn btn-primary">Crear manual</button>
        </form>
        <div className="d-flex gap-1 align-items-end">
          <div><label className="form-label small mb-1">Auto A/B/C</label><input type="number" className="form-control" value={numGrupos} onChange={e=>setNumGrupos(e.target.value)} min={2} max={8} style={{width:80}} /></div>
          <button className="btn btn-success" onClick={crearAuto}>Generar automático</button>
        </div>
      </div>
      <small className="text-muted">Auto reparte equipos del torneo en Round-Robin. Reagrupar: añade a otro grupo (mueve).</small>
      <div className="row mt-3">
        {grupos.map(g=>(
          <div key={g.id} className="col-md-6 mb-3">
            <div className="card">
              <div className="card-header d-flex justify-content-between">{g.nombre} <button className="btn btn-sm btn-outline-danger" onClick={async()=>{ await del(`/grupos/${g.id}`).catch(e=>setMsg(e.message)); load()}}>Borrar (vacío)</button></div>
              <ul className="list-group list-group-flush">
                {(g.equipos||[]).map(eq=>(
                  <li key={eq.id} className="list-group-item d-flex justify-content-between">{eq.nombre} <button className="btn btn-sm btn-outline-secondary" onClick={async()=>{ await del(`/grupos/${g.id}/equipos/${eq.id}`).catch(e=>setMsg(e.message)); load()}}>Quitar</button></li>
                ))}
                {(g.equipos||[]).length===0 && <li className="list-group-item text-muted">Sin equipos</li>}
              </ul>
              <div className="card-body d-flex gap-1">
                <select className="form-select" value={selEquipo} onChange={e=>setSelEquipo(e.target.value)}>
                  <option value="">-- Equipo del torneo --</option>
                  {equipos.map(eq=> <option key={eq.id} value={eq.id}>{eq.nombre}</option>)}
                </select>
                <button className="btn btn-sm btn-outline-primary" onClick={()=>addEquipo(g.id)}>Añadir / Mover</button>
              </div>
            </div>
          </div>
        ))}
      </div>
      {grupos.length===0 && <div className="alert alert-light">Sin grupos. Crea manual o automático.</div>}
    </div>
  )
}

function JornadasTab({torneoId}){
  const [rows,setRows]=useState([]); const [nro,setNro]=useState(''); const [fecha,setFecha]=useState('')
  async function load(){ const r=await get(`/torneos/${torneoId}/jornadas`).catch(()=>({data:[]})); setRows(r.data) }
  useEffect(()=>{ load() },[torneoId])
  async function crear(e){ e.preventDefault(); try{ await post(`/torneos/${torneoId}/jornadas`,{nro:parseInt(nro), fecha}); setNro(''); setFecha(''); load() }catch(er){ alert(er.message) } }
  return (
    <div>
      <form onSubmit={crear} className="d-flex gap-2 mb-3">
        <input className="form-control w-auto" value={nro} onChange={e=>setNro(e.target.value)} placeholder="Nro" type="number" required />
        <input className="form-control w-auto" type="date" value={fecha} onChange={e=>setFecha(e.target.value)} required />
        <button className="btn btn-primary">Crear jornada</button>
      </form>
      <small className="text-muted">Jornada puede tener partidos de varios grupos (grupo se elige al crear partido).</small>
      <ul className="list-group mt-2">{rows.map(j=> <li key={j.id} className="list-group-item">Jornada {j.nro} — {j.fecha} <a href={`/partidos/${j.id}`} className="btn btn-sm btn-outline-secondary ms-2">Ver partidos</a></li>)}</ul>
    </div>
  )
}
