# ReservaLabs - Sistema de Reserva de Laboratórios

Sistema em PHP + MySQL + Bootstrap 5 para cadastro de laboratórios, professores, turmas e reservas.

## Funcionalidades

- Painel com laboratórios ocupados e livres.
- Cadastro de laboratórios.
- Cadastro de professores com RM, nome e e-mail.
- Cadastro de turmas.
- Cadastro de reservas.
- Calendário para escolher a data da reserva.
- Horários organizados por 1ª, 2ª, 3ª, 4ª e 5ª aula, além dos blocos completos.
- Intervalo oficial das 20:50 às 21:10.
- Verificação de conflito de horários.
- Avisos de sucesso e erro.
- Cancelamento de reserva usando `fl_ativo`.
- Reservas canceladas permanecem no banco como histórico.
- Sem `ON DELETE CASCADE`.

## Arquivos

```text
banco.sql
conexao.php
header.php
footer.php
index.php
reservas.php
professores.php
laboratorios.php
turmas.php
style.css
README.md
```

## Banco de dados

Execute o arquivo `banco.sql` no MySQL/phpMyAdmin. A tabela `reserva` possui:

```sql
fl_ativo BOOLEAN NOT NULL DEFAULT TRUE
```

Quando uma reserva é cancelada, o sistema executa `UPDATE` e muda `fl_ativo` para `FALSE`, em vez de apagar o registro.

As consultas do painel e a verificação de conflitos consideram somente reservas ativas.

## Como executar

1. Coloque a pasta no `htdocs` do XAMPP.
2. Ligue Apache e MySQL.
3. Importe `banco.sql` no phpMyAdmin.
4. Acesse `http://localhost/reserva-labs-organizado/`.

A conexão está em `conexao.php`.
