CREATE DATABASE IF NOT EXISTS laboratorio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE laboratorio;

CREATE TABLE IF NOT EXISTS laboratorio (
    cd_laboratorio INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    nm_laboratorio VARCHAR(100) NOT NULL,
    ds_laboratorio VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS professor (
    cd_professor INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    rm_professor VARCHAR(20) DEFAULT NULL,
    nm_professor VARCHAR(100) NOT NULL,
    ds_materia_professor VARCHAR(100),
    email_professor VARCHAR(100)
) AUTO_INCREMENT = 25079;

CREATE TABLE IF NOT EXISTS turma (
    cd_turma INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    nm_turma VARCHAR(100) NOT NULL,
    ds_curso_turma VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS reserva (
    cd_reserva INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    id_laboratorio INT NOT NULL,
    id_professor INT NOT NULL,
    id_turma INT NOT NULL,
    dt_reserva DATE NOT NULL,
    hr_inicio TIME NOT NULL,
    hr_termino TIME NOT NULL,
    fl_ativo BOOLEAN NOT NULL DEFAULT TRUE,

    FOREIGN KEY (id_laboratorio)
        REFERENCES laboratorio(cd_laboratorio),

    FOREIGN KEY (id_professor)
        REFERENCES professor(cd_professor),

    FOREIGN KEY (id_turma)
        REFERENCES turma(cd_turma)
);

INSERT INTO laboratorio (nm_laboratorio, ds_laboratorio) VALUES
('Laboratório 1', '30 computadores'),
('Laboratório 2', '30 computadores'),
('Laboratório 3', '30 computadores'),
('Laboratório 4', '30 computadores');

INSERT INTO professor (cd_professor, rm_professor, nm_professor, ds_materia_professor, email_professor) VALUES
(25079, '25079', 'Matheus Calixto', NULL, NULL),
(25080, '25080', 'Oswaldo', NULL, NULL),
(25081, '25081', 'Augusto', NULL, NULL),
(25082, '25082', 'Moema', NULL, NULL),
(25083, '25083', 'Gean', NULL, NULL);

INSERT INTO turma (nm_turma, ds_curso_turma) VALUES
('1MAD-N', 'MTEC-N Administração'),
('2MAD-N', 'MTEC-N Administração'),
('1MDS-N', 'MTEC-N Desenvolvimento de Sistemas'),
('2MDS-N', 'MTEC-N Desenvolvimento de Sistemas');
