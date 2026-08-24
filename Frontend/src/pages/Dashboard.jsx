import { useAuth } from '../contexts/AuthContext.jsx'
import { Link } from 'react-router-dom'

export default function Dashboard(){
  const {user,isAdmin,misTorneos}=useAuth()
  return (
    <div>
      <h3>Dashboard</h3>
      <p>Bienvenido {user?.email} ({user?.rol})</p>
      {!isAdmin && (
        <div><h5>Mis Torneos</h5>{misTorneos.length===0 ? <div className="alert alert-info">No asignado a torneos</div> : misTorneos.map(t=><Link key={t.id} className="btn btn-sm btn-outline-primary me-1" to={`/torneos/${t.id}`}>{t.nombre||t.id}</Link>)}</div>
      )}
      {isAdmin && <div className="alert alert-success">Admin: gestiona deportes, torneos, equipos, jornadas y auditoría desde el sidebar.</div>}
    </div>
  )
}
