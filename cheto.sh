#!/bin/bash

echo "🚀 Configurando entorno de desarrollo IA Gestor de Datos..."

# 1️⃣ Verificar si Homebrew está instalado
if ! command -v brew &> /dev/null; then
  echo "📦 Instalando Homebrew..."
  /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
fi

# 2️⃣ Verificar si Python 3.11 está instalado
if ! brew list python@3.11 &> /dev/null; then
  echo "🐍 Instalando Python 3.11..."
  brew install python@3.11
else
  echo "✅ Python 3.11 ya está instalado."
fi

# 3️⃣ Detectar ruta de Python 3.11
PYTHON_PATH=$(brew --prefix python@3.11)/bin/python3.11
echo "📍 Usando Python en: $PYTHON_PATH"

# 4️⃣ Crear entorno virtual
cd backend || exit
if [ ! -d "venv" ]; then
  echo "🧱 Creando entorno virtual..."
  $PYTHON_PATH -m venv venv
else
  echo "✅ Entorno virtual ya existe."
fi

# 5️⃣ Activar entorno virtual
echo "🔌 Activando entorno virtual..."
source venv/bin/activate

# 6️⃣ Actualizar pip
echo "⚙️ Actualizando pip..."
pip install --upgrade pip

# 7️⃣ Instalar dependencias
echo "📦 Instalando dependencias necesarias..."
pip install fastapi uvicorn torch torchvision torchaudio transformers scikit-learn --index-url https://download.pytorch.org/whl/cpu

# 8️⃣ Confirmar instalación
echo "✅ Entorno configurado correctamente."
echo "🔥 Puedes iniciar el servidor con:"
echo "   source venv/bin/activate"
echo "   uvicorn app.main:app --host 0.0.0.0 --port 8000 --reload"
