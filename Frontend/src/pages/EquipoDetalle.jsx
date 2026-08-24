import { useEffect, useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import { get, post, del } from '../services/api.js'
import { useAuth } from '../contexts/AuthContext.jsx'

export default function EquipoDetalle(){
  const {id}=useParams()
  const [equipo,setEquipo]=useState(null)
  const [jugadores,setJugadores]=useState([])
  const [nombre,setNombre]=useState(''); const [dorsal,setDorsal]=useState(''); const [msg,setMsg]=useState('')
  const {isAdmin}=useAuth()

  async function load(){
    try{
      const e=await get(`/equipos/${id}`); setEquipo(e.data)
      const j=await get(`/equipos/${id}/jugadores`); setJugadores(j.data)
    }catch(e){ setMsg(e.message) }
  }
  useEffect(()=>{ load() },[id])

  async function addJugador(e){
    e.preventDefault()
    try{
      await post(`/equipos/${id}/jugadores`, {nombre, dorsal: dorsal?parseInt(dorsal):null})
      setNombre(''); setDorsal(''); setMsg('Jugador añadido'); load()
    }catch(er){ setMsg(er.code==='CONFLICT'?'Dorsal ya existe':er.message) }
  }

  if(!equipo) return <div>Cargando equipo...</div>
  return (
    <div>
      <h3>{equipo.nombre} <small className="text-muted">#{equipo.id}</small></h3>
      <p><Link to="/torneos">← Volver a torneos</Link></p>
      {msg && <div className="alert alert-info">{msg}</div>}

      <h5>Jugadores ({jugadores.length})</h5>
      {jugadores.length===0 ? <div className="alert alert-light">Sin jugadores. Añade el primero.</div> :
        <table className="table table-sm table-bordered">
          <thead><tr><th>#</th><th>Nombre</th><th>Dorsal</th>{isAdmin && <th></th>}</tr></thead>
          <tbody>{jugadores.map(j=>(
            <tr key={j.id}><td>{j.id}</td><td>{j.nombre}</td><td>{j.dorsal||'-'}</td>
              {isAdmin && <td><button className="btn btn-sm btn-outline-danger" onClick={async()=>{ await del(`/jugadores/${j.id}`).catch(e=>setMsg(e.message)); load()}}>Borrar</button></td>}
            </tr>
          ))}</tbody>
        </table>
      }

      {isAdmin ? (
        <form onSubmit={addJugador} className="card p-3 mt-3">
          <h6>Añadir jugador (solo nombres propios manuales)</h6>
          <div className="d-flex gap-2">
            <input className="form-control" value={nombre} onChange={e=>setNombre(e.target.value)} placeholder="Nombre jugador" required />
            <input className="form-control w-auto" type="number" value={dorsal} onChange={e=>setDorsal(e.target.value)} placeholder="Dorsal" />
            <button className="btn btn-primary">Añadir</button>
          </div>
          <small className="text-muted">Dorsal único por equipo. Equipo ya seleccionado (#{id}) no se elige.</small>
        </form>
      ) : (
        <div className="alert alert-warning">Solo admin puede añadir jugadores.</div>
      )}
    </div>
  )
}
