import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";

export default function Register() {
  const [formData, setFormData] = useState({
    ci: "",
    nombre: "",
    apellido: "",
    email: "",
    tipo: "",
    usuario_asignado: "",
    contrasena: "",
    especialidad: "",
  });
  const [error, setError] = useState("");
  const [success, setSuccess] = useState(false);
  const navigate = useNavigate();

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const payload = {
        ci: formData.ci,
        nombre: formData.nombre,
        apellido: formData.apellido,
        email: formData.email,
        tipo: formData.tipo,
        usuario_asignado: formData.usuario_asignado,
        contrasena: formData.contrasena,
      };

      if (formData.tipo === "Tecnico") {
        payload.especialidad = formData.especialidad;
      }

      const response = await fetch("/api/usuario/register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json();

      if (data.success) {
        setSuccess(true);
        setTimeout(() => navigate("/"), 2000);
      } else {
        setError(data.message || "Error al registrar usuario");
      }
    } catch (err) {
      setError("Error de conexion con el servidor");
      console.error("Register error:", err);
    }
  };

  return (
    <div className="register-container">
      <h1>Registro de Usuario</h1>
      {error && <div className="error-message">{error}</div>}
      {success && (
        <div className="success-message">
          <p style={{ color: "#56e335", marginBottom: "5px" }}>
            ¡Registro exitoso! Redirigiendo...
          </p>
        </div>
      )}

      <div className="form-group">
        <label>Cédula: </label>
        <input
          type="text"
          name="ci"
          value={formData.ci}
          onChange={handleChange}
          required
          maxLength="10"
        />
      </div>

      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label>Nombre: </label>
          <input
            type="text"
            name="nombre"
            value={formData.nombre}
            onChange={handleChange}
            required
          />
        </div>

        <div className="form-group">
          <label>Apellido: </label>
          <input
            type="text"
            name="apellido"
            value={formData.apellido}
            onChange={handleChange}
            required
          />
        </div>

        <div className="form-group">
          <label>Email: </label>
          <input
            type="email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            required
          />
        </div>

        <div className="form-group">
          <label>Tipo: </label>
          <select
            name="tipo"
            value={formData.tipo}
            onChange={handleChange}
            required
          >
            <option value="" disabled>
              Seleccione...
            </option>
            <option value="Logistica">Logistica</option>
            <option value="Tecnico">Tecnico</option>
          </select>
        </div>

        {formData.tipo === "Tecnico" && (
          <div className="form-group">
            <label>Especialidad: </label>
            <select
              name="especialidad"
              value={formData.especialidad}
              onChange={handleChange}
              required
            >
              <option value="" disabled>
                Seleccione...
              </option>
              <option value="Ensamblador">Ensamblador</option>
              <option value="Comprobador">Comprobador</option>
              <option value="Mantenimiento">Mantenimiento</option>
            </select>
          </div>
        )}

        <div className="form-group">
          <label>Usuario asignado: </label>
          <input
            type="text"
            name="usuario_asignado"
            value={formData.usuario_asignado}
            onChange={handleChange}
            required
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
          />
        </div>

        <button type="submit" className="submit-btn">
          Registrar
        </button>
      </form>

      <br />
      <div className="auth-links">
        <Link to="/">Ir a inicio de sesión</Link>
      </div>
    </div>
  );
}
