from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
import json
import subprocess
import os

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

BASE_CHAT_DIR = "backend/chats"
DATA_FILE = "backend/ventas_data.json"

# Crear carpeta de chats si no existe
os.makedirs(BASE_CHAT_DIR, exist_ok=True)


def get_user_chat_file(username: str):
    """Devuelve la ruta del archivo de chat de un usuario."""
    safe_name = username.replace(" ", "_").lower()
    return os.path.join(BASE_CHAT_DIR, f"chat_{safe_name}.json")


@app.post("/chat")
async def chat(request: Request):
    data = await request.json()
    user_input = data.get("message", "")
    username = data.get("username", "default_user")

    chat_file = get_user_chat_file(username)

    # Leer datos de ejemplo
    with open(DATA_FILE, "r", encoding="utf-8") as f:
        empresa_data = json.load(f)

    # Crear prompt
    prompt = f"""
    Eres un asistente de análisis de datos empresariales.
    Usa los siguientes datos JSON si el usuario pide información concreta:
    {json.dumps(empresa_data, indent=2)}

    Usuario: {username}
    Pregunta: {user_input}
    """

    # Ejecutar Ollama
    try:
        result = subprocess.run(
            ["ollama", "run", "llama3", prompt],
            capture_output=True,
            text=True
        )
        respuesta = result.stdout.strip()
    except Exception as e:
        respuesta = f"Error al conectar con el modelo: {e}"

    # Guardar historial del usuario
    with open(chat_file, "a", encoding="utf-8") as f:
        f.write(json.dumps({"user": user_input, "bot": respuesta}, ensure_ascii=False) + "\n")

    return {"response": respuesta}


@app.post("/reset_chat")
async def reset_chat(request: Request):
    """Borra el historial del usuario logueado"""
    data = await request.json()
    username = data.get("username", "default_user")

    chat_file = get_user_chat_file(username)

    try:
        with open(chat_file, "w", encoding="utf-8") as f:
            f.write("")  # Limpia el archivo del usuario
        return {"message": f"Chat de {username} borrado correctamente"}
    except Exception as e:
        return {"error": str(e)}
