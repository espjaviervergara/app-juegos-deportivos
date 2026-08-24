import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { get, post } from '../services/api.js'
import { useAuth } from '../contexts/AuthContext.jsx'

export default function PartidoDetalle(){
  const {id}=useParams()
  const [partido,setPartido]=useState(null)
  const [resultado,setResultado]=useState(null)
  const [jugadoresA,setJugadoresA]=useState([]); const [jugadoresB,setJugadoresB]=useState([])
  const [goles,setGoles]=useState([]); const [faltas,setFaltas]=useState([]); const [tarjetas,setTarjetas]=useState([])
  const [selJugador,setSelJugador]=useState(''); const [selEquipo,setSelEquipo]=useState(''); const [cantidad,setCantidad]=useState(1); const [tipoTarjeta,setTipoTarjeta]=useState('amarilla')
  const [motivo,setMotivo]=useState(''); const [msg,setMsg]=useState('')
  const {user,isAdmin}=useAuth()

  async function load(){
    try{
      const p=await get(`/partidos/${id}`); setPartido(p.data)
      // cargar jugadores de ambos equipos
      const ja=await get(`/equipos/${p.data.equipoA_id}/jugadores`).catch(()=>({data:[]}))
      const jb=await get(`/equipos/${p.data.equipoB_id}/jugadores`).catch(()=>({data:[]}))
      setJugadoresA(ja.data); setJugadoresB(jb.data)
      if(ja.data.length) setSelEquipo(p.data.equipoA_id)
      // resultado actual
      try{ const r=await get(`/partidos/${id}/resultados`); setResultado(r.data); const d=JSON.parse(r.data.datos||'{}'); setGoles(d.goles||[]); setFaltas(d.faltas||[]); setTarjetas(d.tarjetas||[]) }catch(e){ setResultado(null) }
    }catch(e){ setMsg(e.message) }
  }
  useEffect(()=>{ load() },[id])

  function addGol(){
    if(!selJugador||!selEquipo) return setMsg('Selecciona jugador y equipo')
    setGoles([...goles, {jugadorId:parseInt(selJugador), equipoId:parseInt(selEquipo), cantidad:parseInt(cantidad)||1}])
  }
  function addFalta(){
    if(!selJugador||!selEquipo) return setMsg('Selecciona jugador y equipo')
    setFaltas([...faltas, {jugadorId:parseInt(selJugador), equipoId:parseInt(selEquipo), cantidad:parseInt(cantidad)||1}])
  }
  function addTarjeta(){
    if(!selJugador||!selEquipo) return setMsg('Selecciona jugador y equipo')
    setTarjetas([...tarjetas, {jugadorId:parseInt(selJugador), equipoId:parseInt(selEquipo), tipo:tipoTarjeta}])
  }

  async function proponer(){
    try{
      await post(`/partidos/${id}/resultados`, {goles, faltas, tarjetas})
      setMsg('Resultado PENDIENTE creado'); load()
    }catch(e){ setMsg(e.code==='CONFLICT'?'Ya existe PENDIENTE':e.message) }
  }
  async function aprobar(){ try{ await post(`/partidos/${id}/resultados/aprobar`,{}); setMsg('Aprobado OFICIAL'); load()}catch(e){ setMsg(e.message)} }
  async function rechazar(){ if(!motivo) return setMsg('Motivo requerido'); try{ await post(`/partidos/${id}/resultados/rechazar`,{motivo}); setMsg('Rechazado'); load()}catch(e){ setMsg(e.message)} }

  if(!partido) return <div>Cargando partido...</div>
  const todosJugadores = [...jugadoresA.map(j=>({...j, equipoId:partido.equipoA_id, equipoNombre:'A'})), ...jugadoresB.map(j=>({...j, equipoId:partido.equipoB_id, equipoNombre:'B'}))]

  return (
    <div>
      <h3>Partido #{partido.id} — {partido.equipoA_id} vs {partido.equipoB_id}</h3>
      <p className="text-muted">{partido.fechaHora} — {partido.estado} {resultado && <span className={`badge ${resultado.estado==='OFICIAL'?'bg-success': resultado.estado==='RECHAZADO'?'bg-danger':'bg-warning'}`}>{resultado.estado} v{resultado.version}</span>} {resultado?.motivo_rechazo && <span className="ms-2">Motivo: {resultado.motivo_rechazo}</span>}</p>
      {msg && <div className="alert alert-info">{msg}</div>}

      <div className="row">
        <div className="col-md-6">
          <h6>Goles ({goles.length})</h6>
          <ul className="list-group mb-2">{goles.map((g,i)=><li key={i} className="list-group-item d-flex justify-content-between">Jugador #{g.jugadorId} (Eq {g.equipoId}) x{g.cantidad} <button className="btn btn-sm btn-outline-danger" onClick={()=>setGoles(goles.filter((_,idx)=>idx!==i))}>x</button></li>)}</ul>
          <h6>Faltas ({faltas.length}) <small className="text-muted">distinto a tarjetas</small></h6>
          <ul className="list-group mb-2">{faltas.map((f,i)=><li key={i} className="list-group-item d-flex justify-content-between">Jugador #{f.jugadorId} (Eq {f.equipoId}) x{f.cantidad} <button className="btn btn-sm btn-outline-danger" onClick={()=>setFaltas(faltas.filter((_,idx)=>idx!==i))}>x</button></li>)}</ul>
          <h6>Tarjetas ({tarjetas.length})</h6>
          <ul className="list-group mb-2">{tarjetas.map((t,i)=><li key={i} className="list-group-item d-flex justify-content-between">Jugador #{t.jugadorId} ({t.tipo}) <button className="btn btn-sm btn-outline-danger" onClick={()=>setTarjetas(tarjetas.filter((_,idx)=>idx!==i))}>x</button></li>)}</ul>
        </div>

        <div className="col-md-6">
          <div className="card p-3">
            <h6>Añadir evento (selects de BD)</h6>
            <div className="mb-2">
              <label className="form-label small">Jugador (select de BD)</label>
              <select className="form-select" value={selJugador} onChange={e=>{
                const v=e.target.value; setSelJugador(v);
                const j=todosJugadores.find(x=>x.id==v); if(j) setSelEquipo(j.equipoId)
              }}>
                <option value="">-- Selecciona jugador --</option>
                {todosJugadores.map(j=> <option key={j.id} value={j.id}>{j.nombre} (Eq {j.equipoId} - {j.equipoNombre})</option>)}
              </select>
            </div>
            <div className="mb-2">
              <label className="form-label small">Equipo (auto por jugador)</label>
              <select className="form-select" value={selEquipo} onChange={e=>setSelEquipo(e.target.value)}>
                <option value={partido.equipoA_id}>Equipo A ({partido.equipoA_id})</option>
                <option value={partido.equipoB_id}>Equipo B ({partido.equipoB_id})</option>
              </select>
            </div>
            <div className="d-flex gap-2 mb-2">
              <input type="number" className="form-control w-auto" value={cantidad} onChange={e=>setCantidad(e.target.value)} min={1} placeholder="cantidad" />
              <select className="form-select w-auto" value={tipoTarjeta} onChange={e=>setTipoTarjeta(e.target.value)}>
                <option value="amarilla">Amarilla</option>
                <option value="roja">Roja</option>
              </select>
            </div>
            <div className="d-flex gap-1 flex-wrap">
              <button className="btn btn-sm btn-outline-primary" onClick={addGol}>+ Gol</button>
              <button className="btn btn-sm btn-outline-warning" onClick={addFalta}>+ Falta</button>
              <button className="btn btn-sm btn-outline-danger" onClick={addTarjeta}>+ Tarjeta</button>
            </div>
            <small className="text-muted d-block mt-2">Solo nombres propios (jugador) son manuales al crear equipo; aquí se eligen con select.</small>
          </div>

          <div className="mt-3 d-flex gap-2">
            <button className="btn btn-primary" onClick={proponer}>Proponer resultado (editor)</button>
            {isAdmin && <>
              <button className="btn btn-success" onClick={aprobar}>Aprobar</button>
              <div className="d-flex gap-1">
                <input className="form-control form-control-sm" value={motivo} onChange={e=>setMotivo(e.target.value)} placeholder="motivo rechazo" />
                <button className="btn btn-sm btn-danger" onClick={rechazar}>Rechazar</button>
              </div>
            </>}
          </div>
          {!isAdmin && <small className="text-muted">Solo admin ve Aprobar/Rechazar. Editor solo propone.</small>}
        </div>
      </div>
    </div>
  )
}
