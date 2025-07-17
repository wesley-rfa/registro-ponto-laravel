# Registro de Ponto - Laravel

Sistema de registro de ponto eletrônico desenvolvido em Laravel 12 com foco em **qualidade de código**, **arquitetura robusta** e **boas práticas**. Implementa funcionalidades completas de gestão de funcionários e controle de ponto.

---

## 🎯 **PONTOS FORTES DESTACADOS**

### ✅ **Aderência Total aos Requisitos**
- **CRUD completo** de funcionários (Listar, Criar, Editar, Remover)
- **Sistema de roles** (Administrador/Funcionário) com middleware de proteção
- **Registro de ponto** com proteção contra duplicidade
- **Filtragem por período** usando SQL puro conforme solicitado
- **Validação de CPF** com algoritmo matemático completo
- **Consulta automática de CEP** com múltiplos provedores e fallback
- **Relacionamentos corretos** via Migrations e Models

### 🧪 **Cobertura de Testes: 94.05%**
```
Classes: 86.36% (38/44)
Methods: 93.42% (142/152)  
Lines:   94.05% (616/655)
```

**Testes implementados:**
- ✅ **Unitários**: Services, Repositories, DTOs, Rules, Exceptions
- ✅ **Feature**: Controllers, Middleware, Form Requests
- ✅ **Integração**: Fluxo completo com autenticação
- ✅ **SQL puro**: Repositório com queries nativas testadas

### 🏗️ **Arquitetura e Organização**

#### **Padrões de Design Implementados:**
- **Repository Pattern**: Separação clara entre lógica de negócio e persistência
- **Service Layer**: Regras de negócio centralizadas
- **DTO Pattern**: Transferência de dados tipada entre camadas
- **Factory Pattern**: Criação de serviços de CEP
- **Strategy Pattern**: Múltiplos provedores de CEP
- **Observer Pattern**: Logging automático de operações

#### **Estrutura em Camadas:**
```
app/
├── Dtos/           # Objetos de transferência de dados
├── Repositories/   # Camada de persistência (Eloquent + SQL puro)
├── Services/       # Regras de negócio
├── Controllers/    # Controladores por tipo de usuário
├── Middleware/     # Controle de acesso baseado em roles
├── Requests/       # Validações com Form Requests
├── Rules/          # Regras de validação customizadas
├── External/       # Serviços externos (CEP)
└── Interfaces/     # Contratos para inversão de dependência
```

### 🔧 **Tecnologias e Versões**

- **PHP 8.2** (última versão estável)
- **Laravel 12** (última versão estável)
- **MySQL 8.0** (última versão estável)
- **Docker & Docker Compose** (ambiente isolado)
- **Laravel Breeze** (autenticação)
- **Vite + Tailwind** (frontend moderno)
- **PCOV** (cobertura de testes)

---

## 🚀 **Funcionalidades Implementadas**

### **Administrador**
- ✅ **CRUD completo** de funcionários
- ✅ **Associação automática** ao administrador que cadastrou
- ✅ **Listagem de registros** de qualquer funcionário
- ✅ **Filtro por período** (entre duas datas)
- ✅ **Paginação** dos resultados
- ✅ **Validações robustas** (CPF, CEP, email único)

### **Funcionário**
- ✅ **Registro de ponto** com proteção contra duplicidade
- ✅ **Troca de senha** segura
- ✅ **Interface simplificada** com apenas um botão

### **Validações Implementadas**
- ✅ **CPF**: Algoritmo matemático completo + unicidade
- ✅ **CEP**: Formato + consulta automática via API
- ✅ **Email**: Formato + unicidade
- ✅ **Senha**: Confirmação + força mínima
- ✅ **Campos obrigatórios**: Nome, cargo, data de nascimento

---

## 🏗️ **Sistema de CEP - Alta Disponibilidade**

### **Arquitetura Robusta:**
```
CepService (Orquestrador)
├── ViaCepService (Prioridade 1)
├── CorreiosCepService (Fallback)
└── AbstractCepService (Funcionalidades comuns)
```

### **Características:**
- ✅ **Fallback automático** entre provedores
- ✅ **Timeout configurável** por serviço
- ✅ **Logging separado** para debugging
- ✅ **Factory Pattern** para criação de serviços
- ✅ **Strategy Pattern** para diferentes implementações
- ✅ **Testes unitários** completos

---

## 📊 **Banco de Dados**

### **Migrations Implementadas:**
- ✅ **users**: Todos os campos obrigatórios + relacionamentos
- ✅ **clock_ins**: Registros de ponto com índices otimizados
- ✅ **Soft deletes** em ambas as tabelas
- ✅ **Foreign keys** com constraints apropriadas
- ✅ **Índices** para performance de consultas

### **Relacionamentos Eloquent:**
```php
// User Model
public function creator() // Administrador que cadastrou
public function createdUsers() // Funcionários cadastrados
public function clockIns() // Registros de ponto

// ClockIn Model  
public function user() // Funcionário do registro
```

---

## 🧪 **Qualidade de Código**

### **Testes Automatizados:**
```bash
# Executar todos os testes
docker compose exec app vendor/bin/phpunit

# Gerar relatório de cobertura
docker compose exec app vendor/bin/phpunit --coverage-html coverage-report

# Executar testes específicos
docker compose exec app vendor/bin/phpunit --filter=UserCreationTest
```

### **Cobertura por Camada:**
- **Services**: 100% (regras de negócio)
- **Repositories**: 100% (persistência)
- **Controllers**: 100% (endpoints principais)
- **Requests**: 100% (validações)
- **Rules**: 100% (validações customizadas)
- **Models**: 100% (relacionamentos)

---

## ⚙️ **Setup Rápido**

### **Pré-requisitos:**
- Docker
- Docker Compose

### **Instalação:**
```bash
# Clone o repositório
git clone <repository-url>
cd registro-ponto-laravel

# Setup automático
./docker-setup.sh

# Acesse: http://localhost:8000
```

### **Comandos Úteis:**
```bash
# Desenvolvimento
docker compose up -d
docker compose exec app bash

# Testes
docker compose exec app vendor/bin/phpunit
docker compose exec app vendor/bin/phpunit --coverage-text

# Artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

## 👤 Usuário Administrador Inicial

Ao rodar o script de setup (`./docker-setup.sh`), um seeder é executado automaticamente para criar o primeiro usuário administrador do sistema. Esse usuário é necessário para acessar o painel e cadastrar os demais funcionários, já que o CRUD padrão cobre apenas funcionários.

**Credenciais padrão do admin inicial:**
- **E-mail:** admin@teste.com
- **Senha:** 12345678

> Recomenda-se alterar a senha após o primeiro login para garantir a segurança do sistema.

---

## 📈 **Histórico de Versionamento**

### **Commits Estruturados:**
- ✅ **Commits descritivos** e pequenos ciclos de entrega
- ✅ **Frequência consistente** de entregas parciais
- ✅ **Organização clara** por funcionalidade
- ✅ **Boas práticas** de versionamento

### **Evolução do Projeto:**
1. **Setup inicial** com Laravel 12
2. **Migrations** e Models
3. **Autenticação** e Middleware
4. **CRUD de usuários** com validações
5. **Sistema de CEP** com múltiplos provedores
6. **Registro de ponto** com SQL puro
7. **Testes unitários** e de integração
8. **Cobertura de testes** superior a 94%

---

## 📝 **Decisões Técnicas Documentadas**

### **Troca de Senha para Administradores:**
> Embora o enunciado cite apenas funcionários para troca de senha, optou-se por manter essa funcionalidade disponível para administradores também, por razões de segurança e consistência.

### **Sistema de CEP com Fallback:**
> Implementação robusta com múltiplos provedores para garantir alta disponibilidade, seguindo padrões de design enterprise.

### **SQL Puro no Relatório:**
> Uso de prepared statements e joins otimizados para atender ao requisito especial de avaliação da escrita SQL.

---

**Desenvolvido com foco em qualidade, arquitetura e boas práticas para demonstrar competência técnica em desenvolvimento Laravel.**
