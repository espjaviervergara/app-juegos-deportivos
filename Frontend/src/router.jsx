import { createBrowserRouter, Navigate } from 'react-router-dom'
import { useAuth } from './contexts/AuthContext.jsx'
import Layout from './components/Layout.jsx'
import Torneos from './pages/Torneos.jsx'
import TorneoDetalle from './pages/TorneoDetalle.jsx'
import Calendario from './pages/Calendario.jsx'
import Clasificacion from './pages/Clasificacion.jsx'
import Login from './pages/Login.jsx'
import Dashboard from './pages/Dashboard.jsx'
import Gestion from './pages/Gestion.jsx'
import Auditoria from './pages/Auditoria.jsx'

function RequireAuth({children}){ const {user}=useAuth(); return user?children:<Navigate to="/login"/> }
function RequireAdmin({children}){ const {isAdmin}=useAuth(); return isAdmin?children:<div className="alert alert-danger">403 No autorizado</div> }

export const router = createBrowserRouter([
  { path:'/', element:<Layout><Torneos/></Layout> },
  { path:'/torneos/:id', element:<Layout><TorneoDetalle/></Layout> },
  { path:'/torneos/:id/calendario', element:<Layout><Calendario/></Layout> },
  { path:'/torneos/:id/clasificacion', element:<Layout><Clasificacion/></Layout> },
  { path:'/calendario', element:<Layout><Calendario/></Layout> },
  { path:'/login', element:<Login/> },
  { path:'/dashboard', element:<Layout><RequireAuth><Dashboard/></RequireAuth></Layout> },
  { path:'/admin/deportes', element:<Layout><RequireAuth><RequireAdmin><Gestion tipo="deportes"/></RequireAdmin></RequireAuth></Layout> },
  { path:'/admin/auditoria', element:<Layout><RequireAuth><RequireAdmin><Auditoria/></RequireAdmin></RequireAuth></Layout> },
  { path:'*', element:<div className="p-5">404 Not found</div> },
])
