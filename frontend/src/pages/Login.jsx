import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";

export default function Login() {
  const [formData, setFormData] = useState({
    usuario_asignado: "",
    contrasena: "",
  });
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError("");

    try {
      const response = await fetch("/api/usuario/login", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          usuario_asignado: formData.usuario_asignado.trim(),
          contrasena: formData.contrasena,
        }),
        credentials: "include",
      });

      const text = await response.text();
      let data;

      try {
        data = JSON.parse(text);
      } catch {
        console.error("Respuesta no JSON:", text);
        throw new Error("Respuesta invalida del servidor");
      }

      if (!response.ok) {
        throw new Error(data.message || "Error en la autenticacion");
      }

      if (data.success) {
        localStorage.setItem("user", JSON.stringify(data.usuario));

        // Redireccion directa basada en el tipo de usuario
        if (data.usuario.tipo === "Logistica") {
          window.location.href = "/dashboard/logistica"; // Usamos window.location en lugar de navigate.
        } else if (data.usuario.tipo === "Tecnico") {
          window.location.href = `/dashboard/${data.usuario.Especialidad.toLowerCase()}`;
        } else {
          window.location.href = "/";
        }
      } else {
        setError(data.message || "Credenciales incorrectas");
      }
    } catch (err) {
      setError(err.message || "Error de conexion");
      console.error("Error en login:", err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-container">
      <h1>Inicio de Sesión</h1>
      {error && <div className="error-message">{error}</div>}
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label>Usuario asignado: </label>
          <input
            type="text"
            name="usuario_asignado"
            value={formData.usuario_asignado}
            onChange={handleChange}
            required
            autoComplete="username"
          />
        </div>
        <div className="form-group">
          <label>Contraseña: </label>
          <input
            type="password"
            name="contrasena"
            value={formData.contrasena}
            onChange={handleChange}
            required
            autoComplete="current-password"
          />
        </div>
        <button type="submit" className="submit-btn" disabled={loading}>
          {loading ? "Verificando..." : "Ingresar"}
        </button>
      </form>

      <br />
      <div className="auth-links">
        ¿Nuevos empleados?{" "}
        <Link to="/register">Registre un nuevo usuario aquí</Link>
      </div>
    </div>
  );
}
