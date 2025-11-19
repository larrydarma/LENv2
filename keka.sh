echo "Ejecutando y talacheando, espere..."

cd backend

if [ ! -d "venv" ]; then
 echo "Ejecutando entorno virtual"
 python3 -m venv venv

fi

#Entorno vm
echo "Activando matrix..."
source venv/bin/activate

#Dependencias
echo "Instalando librerias..."
pip install --upgrade pip
pip install -r requirements.txt

#Confirmacion
echo "Todo instalado correctamente c:"

#Ejecucion
echo "Iniciando API en en localhost:8000 "
uvicorn app.main:app --reload
