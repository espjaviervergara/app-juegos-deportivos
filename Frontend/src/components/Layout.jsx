import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext.jsx'

export default function Layout({children}){
  const {user,isAdmin,logout}=useAuth()
  const nav=useNavigate()
  return (
    <div className="d-flex flex-column min-vh-100">
      {/* HEADER colegio */}
      <header className="text-white p-2" style={{background:'linear-gradient(90deg,#0d6efd,#20c997)'}}>
        <div className="d-flex justify-content-between align-items-center">
          <div>
            <strong>IE Ángela María Torres Suárez</strong> <small className="ms-2">Becerril Cesar</small>
            <span className="badge bg-light text-dark ms-2">Juegos Deportivos 2026</span>
          </div>
          <div className="d-flex gap-2 align-items-center">
            <Link to="/" className="btn btn-sm btn-light">🏠 Inicio</Link>
            {user ? (
              <>
                <span className="small d-none d-md-inline">{user.email} ({user.rol})</span>
                <Link to="/dashboard" className="btn btn-sm btn-warning">Dashboard</Link>
                <button className="btn btn-sm btn-outline-light" onClick={async()=>{await logout(); nav('/')}}>Salir</button>
              </>
            ) : (
              <Link to="/login" className="btn btn-sm btn-warning tw-font-bold">Acceso Administrador / Ayudante</Link>
            )}
          </div>
        </div>
      </header>
      <div className="d-flex flex-fill">
        <nav className="bg-dark text-white p-3 d-none d-md-block" style={{width:220}}>
          <h6 className="tw-font-bold">Navegación Estudiantil</h6>
          <ul className="nav flex-column mt-2">
            <li className="nav-item"><a className="nav-link text-white" href="/#programacion">📅 Programación</a></li>
            <li className="nav-item"><a className="nav-link text-white" href="/#tablas">🏆 Tablas</a></li>
            <li className="nav-item"><a className="nav-link text-white" href="/#valores">🤝 Valores</a></li>
            <li className="nav-item"><a className="nav-link text-white" href="/#recomendaciones">💡 Recomendaciones</a></li>
            <li className="nav-item"><Link className="nav-link text-white" to="/torneos">Torneos</Link></li>
            <li className="nav-item"><Link className="nav-link text-white" to="/calendario">Calendario</Link></li>
            {user && <li className="nav-item"><Link className="nav-link text-white" to="/dashboard">Dashboard</Link></li>}
            {isAdmin && <li className="nav-item"><Link className="nav-link text-white" to="/admin/auditoria">Auditoría</Link></li>}
          </ul>
          <div className="mt-4 p-2 bg-primary rounded">
            <small>💡 Tip: ¡Hidrátate y calienta antes de jugar!</small>
          </div>
        </nav>
        <main className="flex-fill p-3 p-md-4 bg-light" style={{minHeight:'calc(100vh - 56px)'}}>{children}</main>
      </div>
    </div>
  )
}
