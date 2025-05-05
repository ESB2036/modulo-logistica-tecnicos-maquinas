import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import ProfileSection from "../../components/ProfileSection";
import NotificacionesList from "../../components/NotificacionesList";
import MaquinaList from "../../components/MaquinaList";

export default function TecnicoComprobador() {
  const [user, setUser] = useState(null);
  const [notificaciones, setNotificaciones] = useState([]);
  const [maquinasComprobando, setMaquinasComprobando] = useState([]);
  const [selectedMaquina, setSelectedMaquina] = useState(null);
  const [mostrarMensaje, setMostrarMensaje] = useState(false);
  const [mensaje, setMensaje] = useState("");
  const [accion, setAccion] = useState("");
  const [mostrarNotificaciones, setMostrarNotificaciones] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const storedUser = localStorage.getItem("user");
    if (!storedUser) {
      navigate("/");
      return;
    }

    const userData = JSON.parse(storedUser);
    if (!storedUser || JSON.parse(storedUser).Especialidad !== "Comprobador") {
      navigate("/");
      return;
    }

    setUser(userData);
    loadData();
  }, [navigate]);

  const loadData = async () => {
    try {
      const notifResponse = await fetch(
        `/api/notificaciones/${
          JSON.parse(localStorage.getItem("user")).ID_Usuario
        }`
      );
      const notifData = await notifResponse.json();
      if (notifData.success) setNotificaciones(notifData.notificaciones);

      const userId = JSON.parse(localStorage.getItem("user")).ID_Usuario;

      const compResponse = await fetch(`/api/maquina/comprobador/${userId}`);
      const compData = await compResponse.json();
      if (compData.success) setMaquinasComprobando(compData.maquinas);
    } catch (err) {
      console.error("Error loading data:", err);
    }
  };

  const handleAccion = async () => {
    if (!selectedMaquina || !accion) return;

    try {
      let endpoint = "";
      if (accion === "distribucion") {
        endpoint = "/api/maquina/mandar-distribucion";
      } else {
        endpoint = "/api/maquina/mandar-reensamblar";
      }

      const response = await fetch(endpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          idMaquina: selectedMaquina.ID_Maquina,
          idRemitente: user.ID_Usuario,
          mensaje: mensaje,
        }),
      });

      const data = await response.json();
      if (data.success) {
        loadData();
        setSelectedMaquina(null);
        setMostrarMensaje(false);
        setMensaje("");
        setAccion("");
      }
    } catch (err) {
      console.error("Error al realizar accion:", err);
    }
  };

  return (
    <div className="dashboard-container">
      <header>
        <h1>Panel de Técnico Comprobador</h1>
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
          additionalFields={[
            { label: "Especialidad", value: user?.Especialidad },
          ]}
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

          {/* Comprobando */}
          <MaquinaList
            title="Comprobando"
            maquinas={maquinasComprobando}
            selectedMaquina={selectedMaquina}
            onSelectMaquina={setSelectedMaquina}
            initiallyExpanded={false}
            emptyMessage="No hay máquinas para comprobar..."
            actionButtons={[
              {
                label: "Mandar a distribución ✅",
                onClick: () => {
                  setAccion("distribucion");
                  setMostrarMensaje(true);
                },
                disabled:
                  !selectedMaquina ||
                  !maquinasComprobando.some(
                    (m) => m.ID_Maquina === selectedMaquina?.ID_Maquina
                  ),
              },
              {
                label: "Mandar a reensamblar ❌",
                onClick: () => {
                  setAccion("reensamblar");
                  setMostrarMensaje(true);
                },
                disabled:
                  !selectedMaquina ||
                  !maquinasComprobando.some(
                    (m) => m.ID_Maquina === selectedMaquina?.ID_Maquina
                  ),
              },
            ]}
          />

          {mostrarMensaje && (
            <div className="message-modal">
              <h3>
                Mensaje{" "}
                {accion === "distribucion"
                  ? "para logística"
                  : "para el técnico ensamblador"}
              </h3>
              <textarea
                value={mensaje}
                onChange={(e) => setMensaje(e.target.value)}
                placeholder="Escribe un mensaje..."
              />
              <div>
                <button
                  onClick={() => {
                    setMostrarMensaje(false);
                    setMensaje("");
                    setAccion("");
                  }}
                >
                  Cancelar
                </button>
                <button onClick={handleAccion}>Enviar</button>
              </div>
            </div>
          )}
        </section>
      </div>
    </div>
  );
}
