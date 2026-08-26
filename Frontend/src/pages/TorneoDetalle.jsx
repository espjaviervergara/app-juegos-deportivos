import { useParams, Link } from 'react-router-dom'
import { useEffect, useState } from 'react'
import { get, post, del } from '../services/api.js'
import { useAuth } from '../contexts/AuthContext.jsx'

export default function TorneoDetalle(){
  const {id}=useParams(); const [t,setT]=useState(null); const {isAdmin}=useAuth(); const [tab,setTab]=useState(isAdmin?'equipos':'calendario')
  useEffect(()=>{ get(`/torneos/${id}`).then(r=>setT(r.data)).catch(()=>{}) },[id])
  useEffect(()=>{ if(t && !isAdmin) setTab('calendario') },[t, isAdmin])
  if(!t) return <div>Cargando...</div>
  const tabs = isAdmin ? ['equipos','grupos','jornadas','calendario','clasificacion'] : ['calendario','clasificacion','equipos']
  return (
    <div>
      <h3>{t.nombre} <small className="text-muted">{t.categoria} {t.formato}</small> {!isAdmin && <span className="badge bg-success ms-2">Vista estudiante (solo lectura)</span>}</h3>
      <ul className="nav nav-tabs mb-3">
        {tabs.map(k=><li key={k} className="nav-item"><button className={`nav-link ${tab===k?'active':''}`} onClick={()=>setTab(k)}>{k}</button></li>)}
      </ul>
      {tab==='equipos' && <EquiposTab id={id} readOnly={!isAdmin} />}
      {tab==='grupos' && (isAdmin ? <GruposTab torneoId={id}/> : <div className="alert alert-info">Solo admin puede gestionar grupos.</div>)}
      {tab==='jornadas' && (isAdmin ? <JornadasTab torneoId={id}/> : <div className="alert alert-info">Solo admin puede gestionar jornadas. Ve a Calendario para ver programación.</div>)}
      {tab==='calendario' && <div><Link to={`/torneos/${id}/calendario`}>Ver calendario completo (cards por jornada → grupo)</Link></div>}
      {tab==='clasificacion' && <Link to={`/torneos/${id}/clasificacion`}>Ver tabla clásica</Link>}
    </div>
  )
}
function EquiposTab({id, readOnly}){
  const [rows,setRows]=useState([]); const [todos,setTodos]=useState([]); const [sel,setSel]=useState(''); const [msg,setMsg]=useState('')
  async function load(){
    const r=await get(`/torneos/${id}/equipos`).catch(()=>({data:[]})); setRows(r.data);
    if(!readOnly){
      const t=await get(`/equipos?page=1&limit=100`).catch(()=>({data:[]})); setTodos(t.data)
    }
  }
  useEffect(()=>{ load() },[id, readOnly])
  async function add(){
    if(!sel) return setMsg('Selecciona equipo')
    try{ await post(`/torneos/${id}/equipos`,{equipoId:parseInt(sel)}); setSel(''); setMsg('Añadido'); load() }catch(e){ setMsg(e.message) }
  }
  const noInscritos = todos.filter(t=> !rows.some(r=>r.id===t.id))
  return (
    <div>
      {msg && <div className="alert alert-info">{msg}</div>}
      {!readOnly && (
        <div className="d-flex gap-2 mb-3">
          <select className="form-select w-auto" value={sel} onChange={e=>setSel(e.target.value)}>
            <option value="">-- Selecciona equipo de BD --</option>
            {noInscritos.map(eq=> <option key={eq.id} value={eq.id}>{eq.nombre}</option>)}
          </select>
          <button className="btn btn-primary" onClick={add}>Añadir a torneo</button>
          <a href="/admin/equipos" className="btn btn-outline-secondary">Crear equipo nuevo</a>
        </div>
      )}
      {readOnly && <div className="alert alert-light">Vista estudiante: solo puedes ver los equipos.</div>}
      <ul className="list-group">
        {rows.map(e=>(
          <li key={e.id} className="list-group-item d-flex justify-content-between align-items-center">
            <span>{e.nombre}</span>
            <span>
              <a href={`/equipos/${e.id}`} className="btn btn-sm btn-outline-primary me-1">Ver jugadores</a>
              <button className="btn btn-sm btn-outline-danger" onClick={async()=>{ await del(`/torneos/${id}/equipos/${e.id}`).catch(er=>setMsg(er.message)); load()}}>Quitar</button>
            </span>
          </li>
        ))}
        {rows.length===0 && <li className="list-group-item text-muted">Sin equipos.</li>}
      </ul>
    </div>
  )
}

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
  const [tipo,setTipo]=useState('ida'); const [ambito,setAmbito]=useState('grupo'); const [jornadaSel,setJornadaSel]=useState(''); const [msg,setMsg]=useState(''); const [showElim,setShowElim]=useState(false); const [numClasificados,setNumClasificados]=useState(4)
  async function load(){ const r=await get(`/torneos/${torneoId}/jornadas`).catch(()=>({data:[]})); setRows(r.data) }
  useEffect(()=>{ load() },[torneoId])
  async function crear(e){ e.preventDefault(); try{ await post(`/torneos/${torneoId}/jornadas`,{nro:parseInt(nro), fecha}); setNro(''); setFecha(''); load() }catch(er){ alert(er.message) } }
  async function generar(){
    try{
      const body={tipo, ambito, jornadaId: jornadaSel?parseInt(jornadaSel):null}
      const r=await post(`/torneos/${torneoId}/fixture/generar`, body)
      setMsg(`Generados ${r.data.creados} partidos (${tipo} ${ambito}${jornadaSel?' en jornada':', sin asignar'})`)
      load()
      setShowElim(true)
    }catch(e){ setMsg(e.message) }
  }
  async function generarEliminatoria(){
    try{
      const r=await post(`/torneos/${torneoId}/fixture/eliminatoria`,{numClasificados:parseInt(numClasificados)})
      setMsg(`Eliminatoria ${r.data.creados} partidos generada`); setShowElim(false)
    }catch(e){ setMsg(e.message) }
  }
  return (
    <div>
      {msg && <div className="alert alert-info">{msg}</div>}
      <form onSubmit={crear} className="d-flex gap-2 mb-3">
        <input className="form-control w-auto" value={nro} onChange={e=>setNro(e.target.value)} placeholder="Nro" type="number" required />
        <input className="form-control w-auto" type="date" value={fecha} onChange={e=>setFecha(e.target.value)} required />
        <button className="btn btn-primary">Crear jornada</button>
      </form>
      <div className="card p-3 mb-3">
        <h6>Generar fixture</h6>
        <div className="d-flex gap-2 flex-wrap align-items-end">
          <div><label className="form-label small">Tipo</label><select className="form-select" value={tipo} onChange={e=>setTipo(e.target.value)}><option value="ida">Ida (1 partido)</option><option value="ida_vuelta">Ida y vuelta (2)</option></select></div>
          <div><label className="form-label small">Ámbito</label><select className="form-select" value={ambito} onChange={e=>setAmbito(e.target.value)}><option value="grupo">Por grupo</option><option value="sin_asignar">Sin asignar (todos)</option></select></div>
          <div><label className="form-label small">Jornada destino</label><select className="form-select" value={jornadaSel} onChange={e=>setJornadaSel(e.target.value)}><option value="">Sin asignar (borrador)</option>{rows.map(j=> <option key={j.id} value={j.id}>Jornada {j.nro} — {j.fecha}</option>)}</select></div>
          <button className="btn btn-success" onClick={generar}>Generar todos vs todos</button>
        </div>
      </div>
      {showElim && <div className="card p-3 mb-3 border-warning">
        <h6>¿Va por eliminación directa?</h6>
        <div className="d-flex gap-2 align-items-end">
          <div><label className="form-label small">Clasificados</label><select className="form-select" value={numClasificados} onChange={e=>setNumClasificados(e.target.value)}><option value={2}>2 (Final)</option><option value={4}>4 (Semis)</option><option value={8}>8 (Cuartos)</option></select></div>
          <button className="btn btn-warning" onClick={generarEliminatoria}>Generar eliminatoria</button>
          <button className="btn btn-outline-secondary" onClick={()=>setShowElim(false)}>No, después</button>
        </div>
      </div>}
      <ul className="list-group mt-2">{rows.map(j=> <li key={j.id} className="list-group-item">Jornada {j.nro} — {j.fecha} <a href={`/torneos/${torneoId}/calendario`} className="btn btn-sm btn-outline-secondary ms-2">Ver calendario</a></li>)}</ul>
    </div>
  )
}
