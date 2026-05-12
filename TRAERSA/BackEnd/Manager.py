from confi import get_engine
from sqlalchemy import text
import hashlib
import json

class AdministradorManager:
    def __init__(self):
        self.engine = get_engine()
    
    #Metodo para hashear las contraseñas
    def _hash_password(self, password):
        """Hashear la contraseña usando SHA-256"""
        return hashlib.sha256(password.encode()).hexdigest()
    
    #Buscar por correo y contraseña
    def buscar_por_correo_contrasena(self, correo, contrasena):
        try:
            contrasena_hash = self._hash_password(contrasena)
            print(f"Buscando usuario: {correo}")  
            print(f"Hash usado: {contrasena_hash}")  
            
            with self.engine.connect() as connection:
                # 1. Buscar en cliente
                query_cliente = text("""
                    SELECT id_cliente, nombre, apellido, correo, direccion, telefono, contrasena,
                        3 AS rol, 'cliente' AS tipo, NULL AS sub_rol
                    FROM clientes
                    WHERE correo = :correo AND contrasena = :contrasena
                """)
                result = connection.execute(query_cliente, {
                    "correo": correo,
                    "contrasena": contrasena_hash
                })
                cliente = result.fetchone()
                if cliente:
                    print("Usuario encontrado como cliente")
                    return cliente

                # 2. Buscar en empleado
                query_empleado = text("""
                    SELECT id_empleados, nombre, apellido, correo, direccion, telefono, contrasena,
                        2 AS rol, 'empleado' AS tipo, sub_rol
                    FROM empleados
                    WHERE correo = :correo AND contrasena = :contrasena
                """)
                result = connection.execute(query_empleado, {
                    "correo": correo,
                    "contrasena": contrasena_hash
                })
                empleado = result.fetchone()
                if empleado:
                    print("Resultado de consulta empleados:", empleado)
                    print("Usuario encontrado como empleado")
                    return empleado
                
                # 3. Buscar en administrador
                query_admin = text("""
                    SELECT id_admin, nombre, apellido, correo, direccion, telefono, contrasena,
                        1 AS rol, 'administrador' AS tipo, NULL AS sub_rol
                    FROM administrador
                    WHERE correo = :correo AND contrasena = :contrasena
                """)
                result = connection.execute(query_admin, {
                    "correo": correo,
                    "contrasena": contrasena_hash
                })
                admin = result.fetchone()
                if admin:
                    print("Usuario encontrado como admin")
                    return admin

                print("Usuario no encontrado en ninguna tabla")
                return None
        except Exception as e:
            print(f"Error en buscar_por_correo_contrasena: {e}")
            return None