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
      {/* HERO */}
      <div className="rounded-4 p-4 p-md-5 mb-4 text-white" style={{background:'linear-gradient(135deg,#0d6efd 0%,#20c997 50%,#ffc107 100%)'}}>
        <div className="row align-items-center">
          <div className="col-md-8">
            <h1 className="tw-text-3xl tw-font-black mb-2">Institución Educativa<br/>Ángela María Torres Suárez</h1>
            <p className="lead mb-1">Becerril Cesar — Donde el deporte forma campeones y buenas personas 🏆</p>
            <p className="mb-3">¡Vive los Juegos Deportivos! Consulta tu programación, alienta a tu equipo y celebra el juego limpio.</p>
            <div className="d-flex gap-2">
              <a href="#programacion" className="btn btn-light btn-lg">⚽ Ver Programación</a>
              <a href="#tablas" className="btn btn-outline-light btn-lg">🏆 Tablas</a>
              <Link to={user ? "/dashboard" : "/login"} className="btn btn-warning btn-lg tw-font-bold">
                {user ? (user.rol==='admin' ? 'Ir al Dashboard' : 'Mi Dashboard') : 'Acceso Administrador / Ayudante'}
              </Link>
            </div>
          </div>
          <div className="col-md-4 text-center d-none d-md-block">
            <div style={{fontSize:80}}>🏅🤝💪</div>
            <small>Juega limpio · Respeta · Diviértete</small>
          </div>
        </div>
      </div>

      {/* VALORES */}
      <div className="row mb-4" id="valores">
        {[
          {icon:'🤝', title:'Respeto', desc:'Saluda, escucha al árbitro y valora al rival.'},
          {icon:'💪', title:'Trabajo en equipo', desc:'Pasa la pelota, apoya y celebra juntos.'},
          {icon:'⚖️', title:'Juego limpio', desc:'Gana con humildad, pierde con dignidad.'},
          {icon:'❤️', title:'Pasión', desc:'Da lo mejor en cada minuto.'},
        ].map(v=>(
          <div key={v.title} className="col-md-3 mb-3">
            <div className="card h-100 text-center border-0 shadow-sm">
              <div className="card-body"><div style={{fontSize:36}}>{v.icon}</div><h6 className="tw-font-bold">{v.title}</h6><small className="text-muted">{v.desc}</small></div>
            </div>
          </div>
        ))}
      </div>

      {/* PROGRAMACION */}
      <div id="programacion" className="d-flex justify-content-between align-items-center mb-3">
        <h3 className="tw-font-bold">📅 Programación</h3>
        <span className="badge bg-success">En vivo</span>
      </div>
      {torneos.length===0 ? <div className="alert alert-info">No hay torneos aún. ¡Pronto anunciaremos los próximos juegos!</div> :
        <div className="row">
          {torneos.map(t=>(
            <div key={t.id} className="col-lg-6 mb-4">
              <div className="card h-100 shadow-sm border-0">
                <div className="card-header bg-white d-flex justify-content-between">
                  <strong>{t.nombre}</strong>
                  <span><span className="badge bg-primary me-1">{t.categoria}</span><span className="badge bg-success">{t.formato}</span></span>
                </div>
                <div className="card-body">
                  <h6>Próximos partidos</h6>
                  {(calendarios[t.id]||[]).length===0 ? <small className="text-muted">Sin partidos programados</small> :
                    <ul className="list-unstyled">
                      {(calendarios[t.id]||[]).slice(0,3).map(p=>{
                        const icono = t.nombre.toLowerCase().includes('fútbol') ? '⚽' : t.nombre.toLowerCase().includes('básquet') ? '🏀' : '🏐'
                        return (
                        <li key={p.id} className="d-flex justify-content-between border-bottom py-2 align-items-center">
                          <span>{icono} {p.equipoA_nombre || p.equipoA_id} vs {p.equipoB_nombre || p.equipoB_id}</span>
                          <small>{new Date(p.fechaHora).toLocaleDateString()} <span className={`badge ${p.estado==='finalizado'?'bg-success':'bg-warning'} ms-1`}>{p.estado}</span></small>
                        </li>
                      )})}
                    </ul>
                  }
                  <div className="mt-3 d-flex gap-2">
                    <Link to={`/torneos/${t.id}`} className="btn btn-sm btn-primary">Ver torneo</Link>
                    <Link to={`/torneos/${t.id}/calendario`} className="btn btn-sm btn-outline-primary">Calendario completo</Link>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      }

      {/* TABLAS */}
      <div id="tablas" className="mt-4">
        <h3 className="tw-font-bold">🏆 Tablas de Posiciones</h3>
        <div className="row">
          {torneos.slice(0,3).map(t=>(
            <div key={t.id} className="col-lg-4 mb-3">
              <div className="card shadow-sm">
                <div className="card-header"><strong>{t.nombre}</strong></div>
                <div className="card-body p-0">
                  {(tablas[t.id]||[]).length===0 ? <div className="p-3 text-muted">Sin datos aún</div> :
                    <table className="table table-sm mb-0">
                      <thead><tr><th>#</th><th>Equipo</th><th>Pts</th></tr></thead>
                      <tbody>{(tablas[t.id]||[]).slice(0,5).map((r,i)=>{
                        const medalla = i===0?'🥇':i===1?'🥈':i===2?'🥉':''
                        return <tr key={r.equipo_id} className={i===0?'table-warning':''}><td>{medalla||i+1}</td><td>{r.equipo}</td><td className="tw-font-bold">{r.puntos}</td></tr>
                      })}</tbody>
                    </table>
                  }
                  <div className="p-2"><Link to={`/torneos/${t.id}/clasificacion`} className="btn btn-sm btn-outline-success w-100">Tabla completa</Link></div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* RECOMENDACIONES */}
      <div className="card mt-4 border-0 shadow-sm" id="recomendaciones">
        <div className="card-body">
          <h5 className="tw-font-bold">💡 Recomendaciones para deportistas</h5>
          <div className="row">
            <div className="col-md-4"><strong>💧 Hidrátate</strong><br/><small>Bebe agua antes, durante y después. Evita gaseosas.</small></div>
            <div className="col-md-4"><strong>🤸 Calienta</strong><br/><small>10 min de movilidad y trote suave antes de jugar.</small></div>
            <div className="col-md-4"><strong>😴 Descansa</strong><br/><small>Duerme 8h, come frutas y respeta al rival siempre.</small></div>
          </div>
        </div>
      </div>

      <div className="alert alert-light mt-4 text-center">
        <strong>Estudiantes:</strong> entra sin login para ver programación y tablas. <strong>Docentes:</strong> usa <Link to="/login">Acceso Administrador / Ayudante</Link> para gestionar torneos y cargar resultados.
      </div>

      {/* FOOTER */}
      <footer className="mt-5 py-4 text-center border-top">
        <small className="text-muted">Hecho en colaboración del área de <strong>Tecnología y Deporte</strong> — Institución Educativa Ángela María Torres Suárez de Becerril Cesar • {new Date().getFullYear()}<br/>Promoviendo el deporte, la sana competencia y la buena actitud deportiva.</small>
      </footer>
    </div>
  )
}
