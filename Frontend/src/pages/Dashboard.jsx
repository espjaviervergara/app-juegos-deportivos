import { useAuth } from '../contexts/AuthContext.jsx'
import { Link } from 'react-router-dom'

export default function Dashboard(){
  const {user,isAdmin,misTorneos}=useAuth()
  return (
    <div>
      <h3>Dashboard</h3>
      <p>Bienvenido <strong>{user?.email}</strong> ({user?.rol})</p>
      
      {isAdmin ? (
        <div>
          <div className="alert alert-success">Admin: acceso total</div>
          <div className="row">
            <div className="col-md-4 mb-2"><Link to="/admin/deportes" className="btn btn-outline-primary w-100">Deportes</Link></div>
            <div className="col-md-4 mb-2"><Link to="/admin/torneos" className="btn btn-outline-primary w-100">Torneos</Link></div>
            <div className="col-md-4 mb-2"><Link to="/admin/equipos" className="btn btn-outline-primary w-100">Equipos</Link></div>
            <div className="col-md-4 mb-2"><Link to="/torneos" className="btn btn-outline-secondary w-100">Ver Torneos</Link></div>
            <div className="col-md-4 mb-2"><Link to="/admin/auditoria" className="btn btn-outline-dark w-100">Auditoría</Link></div>
            <div className="col-md-4 mb-2"><Link to="/" className="btn btn-outline-success w-100">SPA Principal</Link></div>
          </div>
          <div className="mt-3">
            <h6>Flujo rápido</h6>
            <ol className="small">
              <li>Crear Torneo (M/F/Mixto, formato)</li>
              <li>Crear Equipos → attach a torneo</li>
              <li>Crear Jornadas → crear Partidos (valida solapamiento 409)</li>
              <li>Asignar editor a torneo → editor propone resultado → admin aprueba/rechaza</li>
              <li>Ver calendario (cards) y clasificación (tabla) en SPA principal</li>
            </ol>
          </div>
        </div>
      ) : (
        <div>
          <h5>Mis Torneos Asignados</h5>
          {misTorneos.length===0 ? <div className="alert alert-warning">No estás asignado a torneos. Pide al admin que te asigne.</div> :
            <div className="list-group">
              {misTorneos.map(t=>(
                <Link key={t.id||t.torneo_id} to={`/torneos/${t.id||t.torneo_id}`} className="list-group-item list-group-item-action">
                  {t.nombre||'Torneo'} <span className="badge bg-primary float-end">Ver</span>
                </Link>
              ))}
            </div>
          }
          <Link to="/" className="btn btn-primary mt-3">Ver SPA Principal (resultados)</Link>
        </div>
      )}
    </div>
  )
}
