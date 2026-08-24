import { useState } from 'react'
import { useAuth } from '../contexts/AuthContext.jsx'
import { useNavigate } from 'react-router-dom'

export default function Login(){
  const [email,setEmail]=useState('admin@juegos.local'); const [password,setPassword]=useState('Admin123!'); const [err,setErr]=useState(''); const {login}=useAuth(); const nav=useNavigate()
  async function submit(e){ e.preventDefault(); try{ await login(email,password); nav('/dashboard') }catch(er){ setErr(er.message) } }
  return (
    <div className="d-flex justify-content-center align-items-center vh-100 bg-light">
      <form onSubmit={submit} className="card p-4" style={{width:380}}>
        <h4>Login</h4>
        {err && <div className="alert alert-danger">{err}</div>}
        <input className="form-control mb-2" value={email} onChange={e=>setEmail(e.target.value)} placeholder="email" />
        <input className="form-control mb-2" type="password" value={password} onChange={e=>setPassword(e.target.value)} placeholder="password" />
        <button className="btn btn-primary w-100">Entrar</button>
        <small className="text-muted mt-2">429 Rate limited → espera Retry-After</small>
      </form>
    </div>
  )
}
