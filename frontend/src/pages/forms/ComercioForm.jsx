import { useState, useEffect } from "react";

export default function ComercioForm({ onClose, onSuccess }) {
  const [formData, setFormData] = useState({
    nombre: "",
    tipo: "Minorista",
    direccion: "",
    telefono: "",
  });
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [isFormValid, setIsFormValid] = useState(false);

  // Verificar si todos los campos están llenos
  useEffect(() => {
    const { nombre, direccion, telefono } = formData;
    const isValid =
      nombre.trim() !== "" && direccion.trim() !== "" && telefono.trim() !== "";
    setIsFormValid(isValid);
  }, [formData]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    // Verificación adicional por si acaso
    if (!isFormValid) return;

    setLoading(true);
    setError("");

    try {
      const response = await fetch("/api/comercio/register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (data.success) {
        onSuccess();
      } else {
        setError(data.message || "Error al registrar comercio");
      }
    } catch (err) {
      setError("Error de conexion con el servidor");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="modal-overlay">
      <div className="modal-content">
        <h2>Registrar Nuevo Comercio</h2>
        {error && <div className="error-message">{error}</div>}

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
            <label>Tipo: </label>
            <select
              name="tipo"
              value={formData.tipo}
              onChange={handleChange}
              required
            >
              <option value="Minorista">Minorista</option>
              <option value="Mayorista">Mayorista</option>
            </select>
          </div>

          <div className="form-group">
            <label>Dirección: </label>
            <input
              type="text"
              name="direccion"
              value={formData.direccion}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-group">
            <label>Teléfono: </label>
            <input
              type="tel"
              name="telefono"
              placeholder="Formato: 098-765-4321"
              pattern="[0-9]{10}"
              value={formData.telefono}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-actions">
            <button type="submit" disabled={!isFormValid || loading}>
              {loading ? "Registrando..." : "Registrar"}
            </button>
            <button type="button" onClick={onClose}>
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
