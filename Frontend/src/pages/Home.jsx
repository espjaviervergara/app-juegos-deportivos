import { useEffect, useState } from 'react'
import { get } from '../services/api.js'
import { Link } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext.jsx'

export default function Home(){
  const [torneos,setTorneos]=useState([])
  const [calendarios,setCalendarios]=useState({})
  const [tablas,setTablas]=useState({})
  const {user}=useAuth()

  useEffect(()=>{
    get('/torneos?page=1&limit=6').then(r=>{
      setTorneos(r.data)
      r.data.forEach(t=>{
        get(`/torneos/${t.id}/calendario?page=1&limit=3`).then(c=> setCalendarios(prev=>({...prev, [t.id]: c.data}))).catch(()=>{})
        get(`/torneos/${t.id}/clasificaciones?page=1&limit=5`).then(c=> setTablas(prev=>({...prev, [t.id]: c.data}))).catch(()=>{})
      })
    }).catch(()=>{})
  },[])

  return (
    <div>
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2 className="tw-text-2xl tw-font-bold">Resultados en Vivo</h2>
        <Link to={user ? "/dashboard" : "/login"} className="btn btn-primary">
          {user ? (user.rol==='admin' ? 'Ir al Dashboard Admin' : 'Mi Dashboard') : 'Acceso Administrador'}
        </Link>
      </div>

      {torneos.length===0 ? <div className="alert alert-info">No hay torneos aún. Crea uno desde el Dashboard.</div> :
        <div className="row">
          {torneos.map(t=>(
            <div key={t.id} className="col-lg-6 mb-4">
              <div className="card h-100 shadow-sm">
                <div className="card-header d-flex justify-content-between">
                  <strong>{t.nombre}</strong>
                  <span><span className="badge bg-secondary me-1">{t.categoria}</span><span className="badge bg-info">{t.formato}</span></span>
                </div>
                <div className="card-body">
                  <h6>Calendario (últimos)</h6>
                  {(calendarios[t.id]||[]).length===0 ? <small className="text-muted">Sin partidos</small> :
                    <ul className="list-unstyled">
                      {(calendarios[t.id]||[]).slice(0,3).map(p=>(
                        <li key={p.id} className="d-flex justify-content-between border-bottom py-1">
                          <span>{p.equipoA_nombre || p.equipoA_id} vs {p.equipoB_nombre || p.equipoB_id}</span>
                          <small>{new Date(p.fechaHora).toLocaleString()} <span className="badge bg-warning ms-1">{p.estado}</span></small>
                        </li>
                      ))}
                    </ul>
                  }
                  <h6 className="mt-3">Clasificación (top 5)</h6>
                  {(tablas[t.id]||[]).length===0 ? <small className="text-muted">Sin datos</small> :
                    <table className="table table-sm table-striped mb-0">
                      <thead><tr><th>#</th><th>Equipo</th><th>Pts</th><th>GA</th></tr></thead>
                      <tbody>{(tablas[t.id]||[]).slice(0,5).map((r,i)=><tr key={r.equipo_id}><td>{i+1}</td><td>{r.equipo}</td><td className="tw-font-bold">{r.puntos}</td><td>{r.GA}</td></tr>)}</tbody>
                    </table>
                  }
                  <div className="mt-3 d-flex gap-2">
                    <Link to={`/torneos/${t.id}`} className="btn btn-sm btn-outline-primary">Ver torneo</Link>
                    <Link to={`/torneos/${t.id}/calendario`} className="btn btn-sm btn-outline-secondary">Calendario</Link>
                    <Link to={`/torneos/${t.id}/clasificacion`} className="btn btn-sm btn-outline-success">Tabla completa</Link>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      }
      <div className="alert alert-light mt-4">
        <strong>Público:</strong> calendario y clasificaciones sin login. <strong>Gestión:</strong> usa el botón Dashboard para crear torneos, equipos, jornadas y partidos, y gestionar resultados (editor propone, admin aprueba).
      </div>
    </div>
  )
}
