import { useState, useEffect } from "react";

export default function NotificacionesList({
  notificaciones,
  mostrarNotificaciones,
  setMostrarNotificaciones,
  emptyMessage = "No hay notificaciones...",
  user,
}) {
  const [noLeidas, setNoLeidas] = useState(0);

  useEffect(() => {
    // Verificar que user y user.ID_Usuario existan antes de hacer la petición:
    if (!user?.ID_Usuario) return;

    const fetchNoLeidas = async () => {
      try {
        const response = await fetch(
          `/api/notificaciones/no-leidas/${user.ID_Usuario}`
        );
        const data = await response.json();
        if (data.success) setNoLeidas(data.total);
      } catch (error) {
        console.error("Error fetching unread notifications:", error);
      }
    };

    fetchNoLeidas();
  }, [user?.ID_Usuario, notificaciones]); // Uso de encadenamiento opcional

  const handleMarcarLeida = async (id) => {
    try {
      await fetch("/api/notificaciones/marcar-leida", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ idNotificacion: id }),
      });
      setNoLeidas((prev) => (prev > 0 ? prev - 1 : 0));
    } catch (error) {
      console.error("Error marking notification as read:", error);
    }
  };

  // Render condicional si user no está disponible:
  if (!user) return null;

  return (
    <section className="notifications-section">
      <h2 onClick={() => setMostrarNotificaciones(!mostrarNotificaciones)}>
        Notificaciones {noLeidas > 0 && `(${noLeidas})`}
        {mostrarNotificaciones ? "🔽" : "▶️"}
      </h2>
      {mostrarNotificaciones &&
        (notificaciones.length > 0 ? (
          <ul>
            {notificaciones.map((notif) => (
              <li
                key={notif.ID_Notificacion}
                className={`notificacion-item ${
                  notif.Estado === "No leido" ? "no-leida" : ""
                }`}
                onClick={() =>
                  notif.Estado === "No leido" &&
                  handleMarcarLeida(notif.ID_Notificacion)
                }
              >
                <p>
                  <strong>{notif.Tipo}</strong> -{" "}
                  {new Date(notif.Fecha).toLocaleString()}
                  {notif.Mensaje && (
                    <>
                      <br />
                      <span className="notificacion-content">
                        {notif.Mensaje}
                      </span>
                    </>
                  )}
                  <br />
                  <span className="notificacion-content">
                    <strong>Máquina recreativa: </strong> {notif.Nombre_Maquina}
                  </span>
                  <span className="notificacion-content">
                    <strong>Comercio: </strong> {notif.NombreComercio}
                  </span>
                  <span className="notificacion-content">
                    <strong>Dirección: </strong> {notif.DireccionComercio}
                  </span>
                </p>
              </li>
            ))}
          </ul>
        ) : (
          <p>{emptyMessage}</p>
        ))}
    </section>
  );
}
