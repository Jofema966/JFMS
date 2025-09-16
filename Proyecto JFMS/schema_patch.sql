USE base_socios;


-- Registro pendiente para aprobación administrativa
CREATE TABLE IF NOT EXISTS PENDING_SOCIO (
  temp_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  Ci INT(8) NOT NULL,
  Pnom VARCHAR(15) NOT NULL,
  Pape VARCHAR(15) NOT NULL,
  FechNac DATE NOT NULL,
  `Contraseña` INT(9) NOT NULL,
  Email VARCHAR(30) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_pending_ci (Ci),
  UNIQUE KEY uq_pending_email (Email)
);
