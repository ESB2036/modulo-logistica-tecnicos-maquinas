import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import ProfileSection from "../../components/ProfileSection";
import ComercioForm from "../forms/ComercioForm";
import MaquinaForm from "../forms/MaquinaForm";
import NotificacionesList from "../../components/NotificacionesList";
import MaquinaList from "../../components/MaquinaList";

export default function Logistico() {
  const [user, setUser] = useState(null);
  const [notificaciones, setNotificaciones] = useState([]);
  const [mostrarNotificaciones, setMostrarNotificaciones] = useState(false);
  const [maquinasDistribucion, setMaquinasDistribucion] = useState([]);
  const [maquinasOperativas, setMaquinasOperativas] = useState([]);
  const [maquinasRetiradas, setMaquinasRetiradas] = useState([]);
  const [mostrarMaquinasRetiradas, setMostrarMaquinasRetiradas] =
    useState(false);
  const [selectedMaquina, setSelectedMaquina] = useState(null);
  const [mostrarComercioForm, setMostrarComercioForm] = useState(false);
  const [mostrarMaquinaForm, setMostrarMaquinaForm] = useState(false);
  const [mensajeMantenimiento, setMensajeMantenimiento] = useState("");
  const [errorMantenimiento, setErrorMantenimiento] = useState("");
  const [mostrarMensajeMantenimiento, setMostrarMensajeMantenimiento] =
    useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const storedUser = localStorage.getItem("user");
    if (!storedUser) {
      navigate("/");
      return;
    }

    const userData = JSON.parse(storedUser);
    if (userData.tipo !== "Logistica") {
      navigate("/");
      return;
    }

    setUser(userData);
    loadData();
  }, [navigate]);

  const loadData = async () => {
    try {
      // Cargar notificaciones
      const notifResponse = await fetch(
        `/api/notificaciones/${
          JSON.parse(localStorage.getItem("user")).ID_Usuario
        }`
      );
      const notifData = await notifResponse.json();
      if (notifData.success) setNotificaciones(notifData.notificaciones);

      // Cargar máquinas en distribución:
      const distResponse = await fetch("/api/maquina/etapa/Distribucion");
      const distData = await distResponse.json();
      if (distData.success) setMaquinasDistribucion(distData.maquinas);

      // Cargar máquinas operativas:
      const opResponse = await fetch("/api/maquina/estado/Operativa");
      const opData = await opResponse.json();
      if (opData.success) setMaquinasOperativas(opData.maquinas);

      // Cargar máquinas retiradas:
      const retResponse = await fetch("/api/maquina/estado/Retirada");
      const retData = await retResponse.json();
      if (retData.success) setMaquinasRetiradas(retData.maquinas);
    } catch (err) {
      console.error("Error loading data:", err);
    }
  };

  const handlePonerOperativa = async () => {
    if (!selectedMaquina) return;

    try {
      const response = await fetch("/api/maquina/poner-operativa", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ idMaquina: selectedMaquina.ID_Maquina }),
      });

      const data = await response.json();
      if (data.success) {
        loadData();
        setSelectedMaquina(null);
      }
    } catch (err) {
      console.error("Error al poner operativa:", err);
    }
  };

  const handleDarMantenimiento = async () => {
    if (!selectedMaquina || !mensajeMantenimiento) return;

    setErrorMantenimiento("");

    try {
      const response = await fetch("/api/maquina/dar-mantenimiento", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          idMaquina: selectedMaquina.ID_Maquina,
          mensaje: mensajeMantenimiento,
          idLogistica: user.ID_Usuario,
        }),
      });

      const data = await response.json();
      if (data.success) {
        loadData();
        setSelectedMaquina(null);
        setMensajeMantenimiento("");
        setMostrarMensajeMantenimiento(false);
      } else {
        setErrorMantenimiento("No hay técnicos de mantenimiento");
      }
    } catch (err) {
      console.error("Error al dar mantenimiento:", err);
      setErrorMantenimiento("No hay técnicos de mantenimiento");
    }
  };

  return (
    <div className="dashboard-container">
      <header>
        <h1>Panel de Logística</h1>
        <button
          onClick={() => {
            localStorage.removeItem("user");
            navigate("/");
          }}
        >
          Cerrar Sesión
        </button>
      </header>

      <div className="dashboard-sections">
        <hr />

        <ProfileSection
          user={user}
          additionalFields={[{ label: "Zona", value: "Vacío por ahora" }]}
        />

        <hr />

        <NotificacionesList
          notificaciones={notificaciones}
          mostrarNotificaciones={mostrarNotificaciones}
          setMostrarNotificaciones={setMostrarNotificaciones}
          user={user}
        />

        <hr />

        <section className="machines-section">
          <h2>Máquinas Recreativas</h2>

          {/* En Distribución */}
          <MaquinaList
            title="En Distribución"
            maquinas={maquinasDistribucion}
            selectedMaquina={selectedMaquina}
            onSelectMaquina={setSelectedMaquina}
            initiallyExpanded={false}
            emptyMessage="No hay máquinas para distribuir..."
            actionButtons={[
              {
                label: "Poner operativa ✅",
                onClick: handlePonerOperativa,
                disabled:
                  !selectedMaquina ||
                  !maquinasDistribucion.some(
                    (m) => m.ID_Maquina === selectedMaquina?.ID_Maquina
                  ),
              },
            ]}
          />

          {/* En Recaudación */}
          <div>
            <h3>En Recaudación</h3>
            {/* Operativas */}
            <MaquinaList
              title="Operativas"
              maquinas={maquinasOperativas}
              selectedMaquina={selectedMaquina}
              onSelectMaquina={setSelectedMaquina}
              initiallyExpanded={false}
              emptyMessage="No hay máquinas operativas..."
              actionButtons={[
                {
                  label: "Llevar a mantenimiento ⚙️",
                  onClick: () => setMostrarMensajeMantenimiento(true),
                  disabled:
                    !selectedMaquina ||
                    !maquinasOperativas.some(
                      (m) => m.ID_Maquina === selectedMaquina?.ID_Maquina
                    ),
                },
              ]}
            />

            {/* Retiradas - Versión de solo lectura */}
            <div className="machine-list">
              <h3
                style={{ cursor: "pointer" }}
                onClick={() => setMostrarMaquinasRetiradas((prev) => !prev)}
              >
                Retiradas {mostrarMaquinasRetiradas ? "🔽" : "▶️"}
              </h3>
              {mostrarMaquinasRetiradas &&
                (maquinasRetiradas.length > 0 ? (
                  <ul>
                    {maquinasRetiradas.map((maquina) => (
                      <li
                        key={maquina.ID_Maquina}
                        style={{ textDecoration: "line-through" }}
                      >
                        <p>
                          <span className="li-maquina-content">
                            <strong>{maquina.Nombre_Maquina}</strong>
                            <br />
                            <span className="span-comercio-details">
                              <strong>Comercio:</strong>{" "}
                              {maquina.NombreComercio}
                            </span>
                            <br />
                            <span className="span-comercio-details">
                              <strong>Dirección:</strong>{" "}
                              {maquina.DireccionComercio}
                            </span>
                          </span>
                        </p>
                        <br />
                      </li>
                    ))}
                  </ul>
                ) : (
                  <p>No hay máquinas retiradas...</p>
                ))}
            </div>
          </div>
        </section>

        <hr />

        <section className="actions-section">
          <h2>Acciones</h2>
          <button onClick={() => setMostrarComercioForm(true)}>
            Nuevo comercio
          </button>
          <button onClick={() => setMostrarMaquinaForm(true)}>
            Nueva máquina
          </button>
        </section>
      </div>

      {mostrarMensajeMantenimiento && (
        <div className="message-modal">
          <h3>Mensaje para el técnico de mantenimiento</h3>
          {errorMantenimiento && (
            <p style={{ color: "red", marginBottom: "5px" }}>
              {errorMantenimiento}
            </p>
          )}
          <textarea
            value={mensajeMantenimiento}
            onChange={(e) => setMensajeMantenimiento(e.target.value)}
            placeholder="Describe el problema..."
            required
          />
          <div className="modal-actions">
            <button
              onClick={() => {
                setMostrarMensajeMantenimiento(false);
                setMensajeMantenimiento("");
              }}
            >
              Cancelar
            </button>
            <button
              onClick={handleDarMantenimiento}
              disabled={!mensajeMantenimiento}
            >
              Enviar
            </button>
          </div>
        </div>
      )}

      {mostrarComercioForm && (
        <ComercioForm
          onClose={() => setMostrarComercioForm(false)}
          onSuccess={() => {
            setMostrarComercioForm(false);
            loadData();
          }}
        />
      )}

      {mostrarMaquinaForm && (
        <MaquinaForm
          onClose={() => setMostrarMaquinaForm(false)}
          onSuccess={() => {
            setMostrarMaquinaForm(false);
            loadData();
          }}
        />
      )}
    </div>
  );
}
