import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext.jsx'

export default function Layout({children}){
  const {user,isAdmin,logout}=useAuth()
  const nav=useNavigate()
  return (
    <div className="d-flex min-vh-100">
      <nav className="bg-dark text-white p-3" style={{width:220}}>
        <h5 className="tw-font-bold">Juegos Deportivos</h5>
        <ul className="nav flex-column mt-3">
          <li className="nav-item"><Link className="nav-link text-white" to="/">Torneos</Link></li>
          <li className="nav-item"><Link className="nav-link text-white" to="/calendario">Calendario</Link></li>
          {user && <li className="nav-item"><Link className="nav-link text-white" to="/dashboard">Dashboard</Link></li>}
          {isAdmin && <li className="nav-item"><Link className="nav-link text-white" to="/admin/deportes">Deportes</Link></li>}
          {isAdmin && <li className="nav-item"><Link className="nav-link text-white" to="/admin/auditoria">Auditoría</Link></li>}
        </ul>
        <div className="mt-4">
          {user ? <><small>{user.email} ({user.rol})</small><button className="btn btn-sm btn-outline-light w-100 mt-2" onClick={async()=>{await logout(); nav('/login')}}>Salir</button></> : <Link className="btn btn-sm btn-light w-100" to="/login">Login</Link>}
        </div>
      </nav>
      <main className="flex-fill p-4 bg-light">{children}</main>
    </div>
  )
}
