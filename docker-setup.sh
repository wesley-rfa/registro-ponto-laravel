#!/bin/bash

echo "🚀 Configurando ambiente Docker para Laravel..."

# Verifica se o Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Por favor, inicie o Docker e tente novamente."
    exit 1
fi

# Verifica se o .env existe
if [ ! -f .env ]; then
  echo "⚠️  Arquivo .env não encontrado. Copiando do .env.example..."
  cp .env.example .env
fi

# Construir e iniciar os containers
echo "📦 Construindo containers..."
docker-compose up -d --build

# Aguardar o MySQL estar pronto
echo "⏳ Aguardando MySQL estar pronto..."
for i in {1..60}; do
    if docker-compose exec mysql mysqladmin ping -h"localhost" --silent; then
        echo "✅ MySQL está pronto!"
        break
    fi
    echo "⏳ Aguardando MySQL... ($i/60)"
    sleep 3
done

# Aguardar mais um pouco para garantir que o MySQL está totalmente inicializado
echo "⏳ Aguardando MySQL inicializar completamente..."
sleep 5

# Corrigir permissões
echo "🔒 Corrigindo permissões..."
docker-compose exec app chmod -R 775 storage bootstrap/cache

# Configurar cache
echo "⚙️ Configurando cache..."
docker-compose exec app php artisan config:cache

# Rodar as migrations
echo "🗄️ Executando migrações..."
docker-compose exec app php artisan migrate || echo "⛔ Erro ao rodar migrations"

# Publicar o template de paginação customizado
echo "📄 Publicando template de paginação..."
docker-compose exec app php artisan vendor:publish --tag=laravel-pagination --force

# Executar seeders
echo "🌱 Executando seeders..."
docker-compose exec app php artisan db:seed || echo "⛔ Erro ao rodar seeders"

# Gerar chave da aplicação
echo "🔑 Gerando chave da aplicação..."
docker-compose exec app php artisan key:generate --force

# Limpar caches
echo "🧹 Limpando caches..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear

# Instalar dependências do Node.js
echo "📦 Instalando dependências do Node.js..."
docker-compose exec app npm install

# Compilar assets
echo "🎨 Compilando assets..."
docker-compose exec app npm run build

# Finalização
echo ""
echo "✅ Ambiente Docker configurado com sucesso!"
echo "🌐 Acesse: http://localhost:8000"
echo "🗄️ MySQL: localhost:3306"
echo ""
echo "📌 Comandos úteis:"
echo "  docker-compose up -d          # Iniciar containers"
echo "  docker-compose down           # Parar containers"
echo "  docker-compose exec app bash  # Acessar container da aplicação"
echo "  docker-compose logs -f        # Ver logs em tempo real"