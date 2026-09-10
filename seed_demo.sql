-- Dados demonstrativos locais. Seguro para executar mais de uma vez.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO habitats (nome, tipo, capacidade, localizacao, descricao)
VALUES
  ('Floresta Tropical', 'Floresta', 20, 'Setor Leste', 'Ambiente úmido para espécies de mata.'),
  ('Savana Africana', 'Savana', 12, 'Setor Norte', 'Área aberta para grandes mamíferos.'),
  ('Recife de Coral', 'Aquático', 40, 'Aquário Central', 'Tanque de água salgada com controle de parâmetros.'),
  ('Lago de Anfíbios', 'Aquático', 18, 'Setor Sul', 'Área de água doce para anfíbios e pequenos répteis.'),
  ('Viveiro de Aves', 'Viveiro', 30, 'Setor Oeste', 'Viveiro amplo para aves de voo controlado.')
ON DUPLICATE KEY UPDATE tipo = VALUES(tipo), capacidade = VALUES(capacidade), localizacao = VALUES(localizacao), descricao = VALUES(descricao);

INSERT INTO veterinarios (nome, email, crmv, especialidade, telefone)
VALUES
  ('Dra. Marina Costa', 'marina.costa@zoologic.local', 'CRMV-SP 10452', 'Medicina de animais silvestres', '(11) 98888-1001'),
  ('Dr. Rafael Mendes', 'rafael.mendes@zoologic.local', 'CRMV-SP 11873', 'Clínica de mamíferos', '(11) 97777-2002'),
  ('Dra. Camila Nogueira', 'camila.nogueira@zoologic.local', 'CRMV-SP 12604', 'Medicina aviária', '(11) 96666-3003')
ON DUPLICATE KEY UPDATE nome = VALUES(nome), especialidade = VALUES(especialidade), telefone = VALUES(telefone);

INSERT INTO animais (nome, tipo, especie, setor, habitat_id, idade, peso, alimentacao, status, sexo, observacoes)
SELECT demo.nome, demo.tipo, demo.especie, demo.setor, h.id, demo.idade, demo.peso, demo.alimentacao, demo.status, demo.sexo, demo.observacoes
FROM (
  SELECT 'Luna' AS nome, 'Mamífero' AS tipo, 'Onça-pintada' AS especie, 'Terrestre' AS setor, 'Floresta Tropical' AS habitat, 7 AS idade, 68.40 AS peso, 'Carnívoro' AS alimentacao, 'Saudável' AS status, 'F' AS sexo, 'Acompanhamento preventivo em dia.' AS observacoes UNION ALL
  SELECT 'Trovão', 'Mamífero', 'Elefante-africano', 'Terrestre', 'Savana Africana', 18, 4120.00, 'Herbívoro', 'Em Tratamento', 'M', 'Tratamento odontológico em acompanhamento.' UNION ALL
  SELECT 'Pérola', 'Ave', 'Arara-azul', 'Misto', 'Floresta Tropical', 4, 1.35, 'Onívoro', 'Saudável', 'F', 'Revisão nutricional trimestral.' UNION ALL
  SELECT 'Nilo', 'Réptil', 'Iguana-verde', 'Terrestre', 'Floresta Tropical', 6, 4.80, 'Herbívoro', 'Crítico', 'M', 'Em observação intensiva.' UNION ALL
  SELECT 'Coral', 'Peixe', 'Peixe-palhaço', 'Aquático', 'Recife de Coral', 2, 0.18, 'Onívoro', 'Saudável', 'F', 'Parâmetros da água estáveis.' UNION ALL
  SELECT 'Bento', 'Anfíbio', 'Axolote', 'Aquático', 'Lago de Anfíbios', 3, 0.22, 'Carnívoro', 'Saudável', 'M', 'Sem alterações no último exame.'
) AS demo
INNER JOIN habitats h ON h.nome = demo.habitat
WHERE NOT EXISTS (SELECT 1 FROM animais AS existing_animal WHERE existing_animal.nome = demo.nome);
