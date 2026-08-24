import { createContext, useContext, useState, useEffect } from 'react'
import { api, setAccessToken, getAccessToken } from '../services/api.js'

const Ctx = createContext(null)
export const useAuth = ()=> useContext(Ctx)

export function AuthProvider({children}){
  const [user,setUser]=useState(JSON.parse(localStorage.getItem('user')||'null'))
  const [loading,setLoading]=useState(false)
  const [misTorneos,setMisTorneos]=useState([])

  useEffect(()=>{
    const t=getAccessToken(); if(t && user) fetchMis()
  },[])

  async function fetchMis(){
    try{ const r=await api('/mis-torneos'); setMisTorneos(r.data||[]) }catch(e){ setMisTorneos([]) }
  }

  async function login(email,password){
    setLoading(true)
    try{
      const r=await api('/auth/login',{method:'POST',body:{email,password}})
      setAccessToken(r.data.accessToken); localStorage.setItem('user',JSON.stringify(r.data.user)); setUser(r.data.user); await fetchMis(); return r.data.user
    }finally{ setLoading(false) }
  }
  async function logout(){
    try{ await api('/auth/logout',{method:'POST'}) }catch(e){}
    setAccessToken(null); localStorage.removeItem('user'); setUser(null); setMisTorneos([])
  }
  const isAdmin = user?.rol==='admin'
  const canAccessTorneo = (torneoId)=> isAdmin || misTorneos.some(t=>t.id==torneoId || t.torneo_id==torneoId)

  return <Ctx.Provider value={{user,loading,misTorneos,isAdmin,canAccessTorneo,login,logout,fetchMis}}>{children}</Ctx.Provider>
}
