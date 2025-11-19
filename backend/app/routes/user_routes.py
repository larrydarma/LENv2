from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
import json, os

router = APIRouter()
DATA_PATH = "app/data/users.json"

class User(BaseModel):
    name: str
    email: str
    password: str

def load_users():
    if not os.path.exists(DATA_PATH):
        return []
    with open(DATA_PATH, "r") as f:
        return json.load(f)

def save_users(users):
    with open(DATA_PATH, "w") as f:
        json.dump(users, f, indent=4)

@router.post("/register")
def register_user(user: User):
    users = load_users()
    if any(u["email"] == user.email for u in users):
        raise HTTPException(status_code=400, detail="El usuario ya existe")

    users.append(user.dict())
    save_users(users)
    return {"message": "Usuario registrado con éxito"}

@router.get("/users")
def get_users():
    return load_users()
