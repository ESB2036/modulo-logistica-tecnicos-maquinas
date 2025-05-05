import { useNavigate } from "react-router-dom";

export default function ProfileSection({ user, additionalFields = [] }) {
  const navigate = useNavigate();

  if (!user) return null;

  return (
    <section className="profile-section">
      <h2>Perfil</h2>
      <div>
        <p>
          <strong>Cédula:</strong> {user.ci}
        </p>
        <p>
          <strong>Nombre:</strong> {user.nombre} {user.apellido}
        </p>
        {additionalFields.map((field, index) => (
          <p key={index}>
            <strong>{field.label}:</strong> {field.value}
          </p>
        ))}
      </div>
    </section>
  );
}
