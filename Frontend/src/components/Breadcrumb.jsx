import { Link, useNavigate, useLocation } from 'react-router-dom'

export default function Breadcrumb(){
  const nav=useNavigate(); const loc=useLocation()
  const parts = loc.pathname.split('/').filter(Boolean)
  let path=''
  return (
    <nav className="d-flex align-items-center gap-2 mb-3">
      <button className="btn btn-sm btn-outline-secondary" onClick={()=> nav(-1)}>← Atrás</button>
      <button className="btn btn-sm btn-outline-secondary" onClick={()=> nav(1)}>Adelante →</button>
      <ol className="breadcrumb mb-0 ms-2">
        <li className="breadcrumb-item"><Link to="/">Inicio</Link></li>
        {parts.map((p,i)=>{
          path+=`/${p}`
          const isLast=i===parts.length-1
          return <li key={path} className={`breadcrumb-item ${isLast?'active':''}`}>{isLast? p : <Link to={path}>{p}</Link>}</li>
        })}
      </ol>
    </nav>
  )
}
