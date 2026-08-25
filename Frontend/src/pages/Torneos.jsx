import { useEffect, useState } from 'react'
import { get } from '../services/api.js'
import { Link } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext.jsx'

export default function Torneos(){
  const [data,setData]=useState([]); const [page,setPage]=useState(1); const [total,setTotal]=useState(0); const [deporte,setDeporte]=useState(''); const limit=6
  const {isAdmin}=useAuth()
  useEffect(()=>{ get(`/torneos?page=${page}&limit=${limit}${deporte?`&deporteId=${deporte}`:''}`).then(r=>{setData(r.data); setTotal(r.meta.total)}).catch(()=>{}) },[page,deporte])
  return (
    <div>
      <h3>Torneos</h3>
      <div className="d-flex gap-2 mb-3">
        <select className="form-select w-auto" value={deporte} onChange={e=>{setDeporte(e.target.value); setPage(1)}}>
          <option value="">Todos deportes</option><option value="1">Fútbol</option><option value="2">Básquet</option><option value="3">Vóley</option>
        </select>
      </div>
      {data.length===0 ? <div className="alert alert-info">No hay torneos</div> : <div className="row">{data.map(t=>(
        <div key={t.id} className="col-md-4 mb-3"><div className="card"><div className="card-body">
          <h5>{t.nombre}</h5><span className="badge bg-secondary me-1">{t.categoria}</span><span className="badge bg-info">{t.formato}</span>
          <div className="mt-2"><Link className="btn btn-sm btn-primary me-1" to={`/torneos/${t.id}`}>Ver</Link><Link className="btn btn-sm btn-outline-primary me-1" to={`/torneos/${t.id}/calendario`}>Calendario</Link>{isAdmin && <Link className="btn btn-sm btn-warning" to={`/torneos/${t.id}`}>Gestionar</Link>}</div>
        </div></div></div>
      ))}</div>}
      <nav><button className="btn btn-sm btn-outline-secondary me-1" disabled={page<=1} onClick={()=>setPage(p=>p-1)}>Anterior</button><span>Página {page} de {Math.ceil(total/limit)||1}</span><button className="btn btn-sm btn-outline-secondary ms-1" disabled={page>=Math.ceil(total/limit)} onClick={()=>setPage(p=>p+1)}>Siguiente</button></nav>
    </div>
  )
}
