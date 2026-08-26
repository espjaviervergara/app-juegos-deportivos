import { useEffect, useState } from 'react'
import { get, post, del } from '../services/api.js'

export default function Usuarios(){
  const [rows,setRows]=useState([]); const [nombre,setNombre]=useState(''); const [email,setEmail]=useState(''); const [password,setPassword]=useState(''); const [rol,setRol]=useState('editor'); const [msg,setMsg]=useState('')
  async function load(){ const r=await get('/usuarios').catch(()=>({data:[]})); setRows(r.data) }
  useEffect(()=>{ load() },[])
  async function crear(e){
    e.preventDefault();
    try{ await post('/usuarios',{nombre,email,password,rol}); setNombre(''); setEmail(''); setPassword(''); setMsg('Usuario creado'); load() }catch(er){ setMsg(er.message) }
  }
  return (
    <div>
      <h4>Gestión de Usuarios (solo admin)</h4>
      {msg && <div className="alert alert-info">{msg}</div>}
      <form onSubmit={crear} className="card p-3 mb-3">
        <div className="row">
          <div className="col-md-3"><label className="form-label small">Nombre</label><input className="form-control" value={nombre} onChange={e=>setNombre(e.target.value)} required /></div>
          <div className="col-md-3"><label className="form-label small">Email</label><input className="form-control" type="email" value={email} onChange={e=>setEmail(e.target.value)} required /></div>
          <div className="col-md-3"><label className="form-label small">Password</label><input className="form-control" type="password" value={password} onChange={e=>setPassword(e.target.value)} required /></div>
          <div className="col-md-2"><label className="form-label small">Rol</label><select className="form-select" value={rol} onChange={e=>setRol(e.target.value)}><option value="editor">Ayudante (editor)</option><option value="admin">Administrador</option></select></div>
          <div className="col-md-1 d-flex align-items-end"><button className="btn btn-primary w-100">Crear</button></div>
        </div>
        <small className="text-muted">Solo admin puede crear segundo admin o ayudante. El ayudante solo coloca resultados.</small>
      </form>
      <table className="table table-sm table-bordered">
        <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th></th></tr></thead>
        <tbody>{rows.map(u=>(
          <tr key={u.id}><td>{u.nombre}</td><td>{u.email}</td><td><span className={`badge ${u.rol==='admin'?'bg-danger':'bg-primary'}`}>{u.rol}</span></td><td><button className="btn btn-sm btn-outline-danger" onClick={async()=>{ await del(`/usuarios/${u.id}`).catch(e=>setMsg(e.message)); load()}}>Borrar</button></td></tr>
        ))}</tbody>
      </table>
    </div>
  )
}
