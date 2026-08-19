ALTER TABLE Restriccion_Docente
    ADD COLUMN ID_Hc INT NULL AFTER ID_D,
    ADD UNIQUE KEY UQ_Restriccion_Docente_Horario (ID_Hc),
    ADD CONSTRAINT FK_Restriccion_Docente_Horario
        FOREIGN KEY (ID_Hc) REFERENCES HorarioFinal (ID_Hc)
        ON DELETE CASCADE ON UPDATE CASCADE;
