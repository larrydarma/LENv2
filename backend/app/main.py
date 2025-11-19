from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import json
import random
import datetime

from app.ia_model import generar_respuesta

app = FastAPI()

#   Permitir solicitudes desde tu frontend local
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

DATA_FILE = "app/data.json"

class Mensaje(BaseModel):
    usuario: str
    mensaje: str

# Función auxiliar para guardar en JSON
def guardar_historial(data):
    with open(DATA_FILE, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=4, ensure_ascii=False)

# Cargar historial existente
def cargar_historial():
    try:
        with open(DATA_FILE, "r", encoding="utf-8") as f:
            return json.load(f)
    except FileNotFoundError:
        return []

@app.get("/")
def home():
    return {"status": "API activa", "ruta": "/chat para usar el modelo"}

@app.post("/chat")
async def chat(mensaje: Mensaje):
    historial = cargar_historial()

    respuesta = generar_respuesta(mensaje.mensaje)

    entrada = {
        "usuario": mensaje.usuario,
        "mensaje": mensaje.mensaje,
        "respuesta": respuesta,
        "fecha": datetime.datetime.now().isoformat()
    }

    historial.append(entrada)
    guardar_historial(historial)

    return {"respuesta": respuesta}
