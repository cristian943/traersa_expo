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
        
    #Buscar Reportes de clientes 
    def buscar_reportes_clientes(self):
        try:
            with self.engine.connect() as connection:
                query = text("""
                    SELECT r.id , r.descripcion, r.fecha, c.nombre AS nombre_cliente, c.apellido AS apellido_cliente
                    FROM reportes r
                    JOIN clientes c ON r.id_cliente = c.id_cliente
                """)
                result = connection.execute(query)
                reportes = result.fetchall()
                return reportes
        except Exception as e:
            print(f"Error en buscar_reportes_clientes: {e}")
            return None 
    
    #guardar reporte de cliente 
    def guardar_reporte_cliente(self, descripcion, id_cliente):
        try:
            with self.engine.connect() as connection:
                query = text("""
                    INSERT INTO reportes (descripcion, fecha, id_cliente)
                    VALUES (:descripcion, NOW(), :id_cliente)
                """)
                connection.execute(query, {
                    "descripcion": descripcion,
                    "id_cliente": id_cliente
                })
                return True
        except Exception as e:
            print(f"Error en guardar_reporte_cliente: {e}")
            return False
        
    #eliminar reporte de cliente
    def eliminar_reporte_cliente(self, id_reporte):
        try:
            with self.engine.connect() as connection:
                query = text("""
                    DELETE FROM reportes
                    WHERE id = :id_reporte
                """)
                connection.execute(query, {
                    "id_reporte": id_reporte
                })
                return True
        except Exception as e:
            print(f"Error en eliminar_reporte_cliente: {e}")
            return False
        
    #editar reporte de cliente
    def editar_reporte_cliente(self, id_reporte, nueva_descripcion):
        try:
            with self.engine.connect() as connection:
                query = text("""
                    UPDATE reportes
                    SET descripcion = :nueva_descripcion, fecha = NOW()
                    WHERE id = :id_reporte
                """)
                connection.execute(query, {
                    "nueva_descripcion": nueva_descripcion,
                    "id_reporte": id_reporte
                })
                return True
        except Exception as e:
            print(f"Error en editar_reporte_cliente: {e}")
            return False
    
    #ver Todos los servicios junto con la informacion del cliente que los solicito
    def ver_servicios_con_informacion_cliente(self):
        try:
            with self.engine.connect() as connection:
                query = text("""
                    SELECT s.id_servicio, s.tipo_servicio, s.descripcion AS descripcion_servicio, s.fecha_solicitud, s.estado,
                           c.nombre AS nombre_cliente, c.apellido AS apellido_cliente, c.correo AS correo_cliente
                    FROM servicios s
                    JOIN clientes c ON s.id_cliente = c.id_cliente
                """)
                result = connection.execute(query)
                servicios = result.fetchall()
                return servicios
        except Exception as e:
            print(f"Error en ver_servicios_con_informacion_cliente: {e}")
            return None
    
    #guardar servicio nuevo
    def guardar_servicio(self, tipo_servicio, descripcion, id_cliente):
        try:
            with self.engine.connect() as connection:
                query = text("""
                    INSERT INTO servicios (tipo_servicio, descripcion, fecha_solicitud, estado, id_cliente)
                    VALUES (:tipo_servicio, :descripcion, NOW(), 'activo', :id_cliente)
                """)
                connection.execute(query, {
                    "tipo_servicio": tipo_servicio,
                    "descripcion": descripcion,
                    "id_cliente": id_cliente
                })
                return True
        except Exception as e:
            print(f"Error en guardar_servicio: {e}")
            return False
        
    #editar el estado del servicio 
    def editar_estado_servicio(self, id_servicio, nuevo_estado):
        try:
            with self.engine.connect() as connection:
                query = text("""
                    UPDATE servicios
                    SET estado = :nuevo_estado
                    WHERE id_servicio = :id_servicio
                """)
                connection.execute(query, {
                    "nuevo_estado": nuevo_estado,
                    "id_servicio": id_servicio
                })
                return True
        except Exception as e:
            print(f"Error en editar_estado_servicio: {e}")
            return False
    

    
