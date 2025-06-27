# 🕹️ Type Hacker – Jogo de Digitação Estilo Hacker (Back-end)

## 🎯 Visão Geral
Este repositório/diretório contém o código do **Back-end** do projeto Type Hacker. Ele é responsável por fornecer uma API RESTful robusta que o Front-end (desenvolvido em React/Vite) consome para todas as operações dinâmicas, como autenticação de usuários, gerenciamento de pontuações, dados de ligas e persistência de informações no banco de dados.

O principal objetivo deste Back-end é processar requisições, aplicar regras de negócio, interagir de forma segura com o banco de dados MySQL e retornar respostas estruturadas (JSON) para o cliente.

---

## 👥 Equipe do Projeto
Este projeto foi desenvolvido por:

- Danniel Eduardo Dorox - *(https://github.com/D0ROX)*
- Gabriel Silva Costa - *(https://github.com/gabrielcosta07)*
- Reinaldo Castellano - *(https://github.com/CastellPg)*
- Murilo Santos - *(https://github.com/murilossx)*

---

## 🏗️ Estrutura do Projeto
O projeto Type Hacker é conscientemente dividido em duas partes principais para melhor organização e desenvolvimento:

### ⚙️ Back-end (Este Repositório)
- **Responsabilidades:** Implementação da API REST, lógica de autenticação, validação de dados do servidor, processamento de pontuações, gerenciamento de ligas, interação e persistência de dados com o banco de dados MySQL.
- **Tecnologias Principais:** PHP, MySQL, Servidor Apache (via XAMPP).


### 🖥️ Front-end (Repositório Separado)
- **Responsabilidades:** Interface do usuário (UI), experiência do usuário (UX), lógica de apresentação do jogo, e consumo dos endpoints desta API Back-end para funcionalidades dinâmicas.
- **Tecnologias Principais:** React, Vite, CSS.
- [👉 Repositório do Front-end](https://github.com/gabrielcosta07/Trabalho-WEB1--JOGO-Front)
---

## 🛠️ Tecnologias Utilizadas

| Camada         | Tecnologia        | Função                                                              |
|----------------|-------------------|---------------------------------------------------------------------|
| Linguagem      | **PHP** | **Lógica da API REST, manipulação de dados, regras de negócio.** |
| Banco de Dados | **MySQL** | **Armazenamento persistente de usuários, partidas, pontuações, ligas.** |
| Servidor Web   | Apache (via XAMPP)| Servir os scripts PHP e receber requisições HTTP.                   |
| Formato de Dados| JSON              | Padrão para troca de informações entre Back-end e Front-end.         |

---

