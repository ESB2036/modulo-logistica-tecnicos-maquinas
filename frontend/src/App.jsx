import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Logistico from "./pages/dashboard/Logistico";
import TecnicoEnsamblador from "./pages/dashboard/TecnicoEnsamblador";
import TecnicoComprobador from "./pages/dashboard/TecnicoComprobador";
import TecnicoMantenimiento from "./pages/dashboard/TecnicoMantenimiento";

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/register" element={<Register />} />

        {/* Rutas de dashboard */}
        <Route path="/dashboard/logistica" element={<Logistico />} />
        <Route path="/dashboard/ensamblador" element={<TecnicoEnsamblador />} />
        <Route path="/dashboard/comprobador" element={<TecnicoComprobador />} />
        <Route
          path="/dashboard/mantenimiento"
          element={<TecnicoMantenimiento />}
        />

        {/* Ruta por defecto */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
