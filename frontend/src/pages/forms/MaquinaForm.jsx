import { useState, useEffect } from "react";

export default function MaquinaForm({ onClose, onSuccess }) {
  const [formData, setFormData] = useState({
    nombre: "",
    tipo: "",
    idComercio: "",
  });
  const [comercios, setComercios] = useState([]);
  const [ensambladores, setEnsambladores] = useState([]);
  const [comprobadores, setComprobadores] = useState([]);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const user = JSON.parse(localStorage.getItem("user")); // Obtenemos el usuario logueado.

  useEffect(() => {
    const loadData = async () => {
      try {
        // Cargar comercios
        const comerciosRes = await fetch("/api/comercio/all");
        const comerciosData = await comerciosRes.json();
        if (comerciosData.success) setComercios(comerciosData.comercios);

        // Cargar tecnicos ensambladores
        const ensRes = await fetch("/api/usuario/tecnicos/Ensamblador");
        const ensData = await ensRes.json();
        if (ensData.success) setEnsambladores(ensData.tecnicos);

        // Cargar tecnicos comprobadores
        const compRes = await fetch("/api/usuario/tecnicos/Comprobador");
        const compData = await compRes.json();
        if (compData.success) setComprobadores(compData.tecnicos);
      } catch (err) {
        console.error("Error loading data:", err);
      }
    };

    loadData();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (
      !formData.idComercio ||
      ensambladores.length === 0 ||
      comprobadores.length === 0
    ) {
      setError(
        "Debe haber al menos un comercio, un tecnico ensamblador y un tecnico comprobador registrados"
      );
      return;
    }

    setLoading(true);
    setError("");

    try {
      const response = await fetch("/api/maquina/register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          ...formData,
          idUsuarioLogistica: user.ID_Usuario,
        }),
      });

      const data = await response.json();

      if (data.success) {
        onSuccess();
      } else {
        setError(data.message || "Error al registrar maquina");
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
        <h2>Registrar Nueva Máquina Recreativa</h2>
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
            <input
              type="text"
              name="tipo"
              value={formData.tipo}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-group">
            <label>Comercio: </label>
            <select
              name="idComercio"
              value={formData.idComercio}
              onChange={handleChange}
              required
            >
              <option value="">Seleccione un comercio</option>
              {comercios.map((comercio) => (
                <option key={comercio.ID_Comercio} value={comercio.ID_Comercio}>
                  {comercio.Nombre} ({comercio.Tipo})
                </option>
              ))}
            </select>
          </div>

          <div className="form-group">
            <label>Técnico Ensamblador: </label>
            <input
              type="text"
              value={
                ensambladores[0]
                  ? `${ensambladores[0].nombre} ${ensambladores[0].apellido}`
                  : "No hay tecnicos ensambladores"
              }
              disabled
            />
          </div>

          <div className="form-group">
            <label>Técnico Comprobador: </label>
            <input
              type="text"
              value={
                comprobadores[0]
                  ? `${comprobadores[0].nombre} ${comprobadores[0].apellido}`
                  : "No hay tecnicos comprobadores"
              }
              disabled
            />
          </div>

          <div className="form-actions">
            <button
              type="submit"
              disabled={
                loading ||
                !formData.idComercio ||
                ensambladores.length === 0 ||
                comprobadores.length === 0
              }
            >
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
