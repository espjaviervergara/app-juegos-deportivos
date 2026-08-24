import React from 'react'
import ReactDOM from 'react-dom/client'
import { RouterProvider } from 'react-router-dom'
import { router } from './router.jsx'
import { AuthProvider } from './contexts/AuthContext.jsx'
import 'bootstrap/dist/css/bootstrap.min.css'
import './index.css'

ReactDOM.createRoot(document.getElementById('root')).render(
  <AuthProvider><RouterProvider router={router} /></AuthProvider>
)
