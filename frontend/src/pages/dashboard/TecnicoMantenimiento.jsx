import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import ProfileSection from "../../components/ProfileSection";
import NotificacionesList from "../../components/NotificacionesList";
import MaquinaList from "../../components/MaquinaList";

export default function TecnicoMantenimiento() {
  const [user, setUser] = useState(null);
  const [notificaciones, setNotificaciones] = useState([]);
  const [maquinasMantenimiento, setMaquinasMantenimiento] = useState([]);
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
    if (
      !storedUser ||
      JSON.parse(storedUser).Especialidad !== "Mantenimiento"
    ) {
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
      const mantResponse = await fetch(`/api/maquina/mantenimiento/${userId}`);
      const mantData = await mantResponse.json();
      if (mantData.success) setMaquinasMantenimiento(mantData.maquinas);
    } catch (err) {
      console.error("Error loading data:", err);
    }
  };

  const handleAccion = async () => {
    if (!selectedMaquina || !accion) return;

    try {
      const response = await fetch("/api/maquina/finalizar-mantenimiento", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          idMaquina: selectedMaquina.ID_Maquina,
          idRemitente: user.ID_Usuario,
          exito: accion === "operativa",
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
      console.error("Error al finalizar mantenimiento:", err);
    }
  };

  return (
    <div className="dashboard-container">
      <header>
        <h1>Panel de Técnico de Mantenimiento</h1>
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

          <MaquinaList
            title="En Mantenimiento"
            maquinas={maquinasMantenimiento}
            selectedMaquina={selectedMaquina}
            onSelectMaquina={setSelectedMaquina}
            initiallyExpanded={false}
            emptyMessage="No hay máquinas para dar mantenimiento..."
            actionButtons={[
              {
                label: "Dar de alta ✅",
                onClick: () => {
                  setAccion("operativa");
                  setMostrarMensaje(true);
                },
                disabled:
                  !selectedMaquina ||
                  !maquinasMantenimiento.some(
                    (m) => m.ID_Maquina === selectedMaquina?.ID_Maquina
                  ),
              },
              {
                label: "Dar de baja ❌",
                onClick: () => {
                  setAccion("reensamblar");
                  setMostrarMensaje(true);
                },
                disabled:
                  !selectedMaquina ||
                  !maquinasMantenimiento.some(
                    (m) => m.ID_Maquina === selectedMaquina?.ID_Maquina
                  ),
              },
            ]}
          />

          {mostrarMensaje && (
            <div className="message-modal">
              <h3>Mensaje para logística</h3>
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
