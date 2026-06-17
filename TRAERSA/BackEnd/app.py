from flask import Flask, request, jsonify
from flask_cors import CORS

from Manager import AdministradorManager

app = Flask(__name__)
CORS(app)


admin_manager = AdministradorManager()

#Inicio de sesion
@app.route('/sesion', methods=['POST'])
def iniciar_sesion():
    try:
        data = request.get_json()

        if 'correo' not in data or 'contrasena' not in data:
            return jsonify({'error': 'Correo y contraseña son requeridos'}), 400

        usuario = admin_manager.buscar_por_correo_contrasena(
            correo=data['correo'],
            contrasena=data['contrasena']
        )

        print("Usuario encontrado:", usuario)

        if usuario:
            columnas = ['id', 'nombre', 'apellido', 'correo', 'direccion', 'telefono', 'contrasena', 'rol', 'tipo', 'sub_rol']
            usuario_dict = dict(zip(columnas, usuario))

            tipo = usuario_dict['tipo']
            rol = usuario_dict['rol']
            sub_rol = usuario_dict['sub_rol']

            # Determinar destino según rol y subrol
            if rol == 1:
                destino = 'administrador'
            elif rol == 2:
                destino = 'empleado'
            elif rol == 3:
                destino = 'cliente'
            else:
                destino = 'otro'

            respuesta = {
                'mensaje': 'Login exitoso',
                'tipo': tipo,
                'rol': rol,
                'data': destino
            }
            #Se guarda el id segun sea su rol o sub rol
            if destino == 'clientes':
                respuesta['id_cliente'] = usuario_dict['id']
            elif destino == 'administrador':
                respuesta['id_admin'] = usuario_dict['id']
            elif destino == 'empleado':
                respuesta['id_empleado'] = usuario_dict['id']
            else:
                respuesta['mensaje'] = 'Rol no reconocido'

            return jsonify(respuesta), 200

        else:
            return jsonify({'error': 'Credenciales incorrectas'}), 401

    except Exception as e:
        return jsonify({'error': str(e)}), 500

#obtener reportes de clientes
@app.route('/reportes', methods=['GET'])
def obtener_reportes_clientes():
    try:
        reportes = admin_manager.buscar_reportes_clientes()#utiliza la funcion buscar_reportes_clientes del manager que contiene la consulta sql
        return jsonify(reportes), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500

#guardar reporte de cliente
@app.route('/reportes', methods=['POST'])
def guardar_reporte_cliente():
    try:
        data = request.get_json() 

        if 'descripcion' not in data or 'id_cliente' not in data: 
            return jsonify({'error': 'Descripción e ID de cliente son requeridos'}), 400

        descripcion = data['descripcion']
        id_cliente = data['id_cliente']

        resultado = admin_manager.guardar_reporte_cliente(descripcion, id_cliente)

        if resultado:
            return jsonify({'mensaje': 'Reporte guardado exitosamente'}), 201
        else:
            return jsonify({'error': 'No se pudo guardar el reporte'}), 500

    except Exception as e:
        return jsonify({'error': str(e)}), 500
    
#eliminar reporte de cliente 
@app.route('/reportes/<int:id_reporte>', methods=['DELETE'])
def eliminar_reporte_cliente(id_reporte):
    try:
        resultado = admin_manager.eliminar_reporte_cliente(id_reporte)

        if resultado:
            return jsonify({'mensaje': 'Reporte eliminado exitosamente'}), 200
        else:
            return jsonify({'error': 'No se pudo eliminar el reporte'}), 500

    except Exception as e:
        return jsonify({'error': str(e)}), 500

#editar reporte de cliente
@app.route('/reportes/<int:id_reporte>', methods=['PUT'])
def editar_reporte_cliente(id_reporte):
    try:
        data = request.get_json()

        if 'nueva_descripcion' not in data:
            return jsonify({'error': 'Nueva descripción es requerida'}), 400

        nueva_descripcion = data['nueva_descripcion']

        resultado = admin_manager.editar_reporte_cliente(id_reporte, nueva_descripcion)

        if resultado:
            return jsonify({'mensaje': 'Reporte editado exitosamente'}), 200
        else:
            return jsonify({'error': 'No se pudo editar el reporte'}), 500

    except Exception as e:
        return jsonify({'error': str(e)}), 500

#ver servicios activos junto con la informacion del cliente que los solicito
@app.route('/servicios-activos', methods=['GET'])
def ver_servicios_activos():
    try:
        servicios = admin_manager.ver_servicios_activos()
        return jsonify(servicios), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500

#ver todos los servicios junto con la informacion del cliente que los solicito
@app.route('/servicios', methods=['GET'])
def ver_servicios_con_informacion_cliente():
    try:
        servicios = admin_manager.ver_servicios_con_informacion_cliente()
        return jsonify(servicios), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500
    
#guardar un servicio nuevo junto con la informacion del cliente que lo solicito
@app.route('/servicios', methods=['POST'])
def guardar_servicio_con_informacion_cliente():
    try:
        data = request.get_json()

        if 'descripcion' not in data or 'id_cliente' not in data:
            return jsonify({'error': 'Descripción e ID de cliente son requeridos'}), 400

        descripcion = data['descripcion']
        id_cliente = data['id_cliente']

        resultado = admin_manager.guardar_servicio_con_informacion_cliente(descripcion, id_cliente)

        if resultado:
            return jsonify({'mensaje': 'Servicio guardado exitosamente'}), 201
        else:
            return jsonify({'error': 'No se pudo guardar el servicio'}), 500

    except Exception as e:
        return jsonify({'error': str(e)}), 500

#editar el estado del servicio 
@app.route('/servicios/<int:id_servicio>/estado', methods=['PUT'])
def editar_estado_servicio(id_servicio):
    try:
        data = request.get_json()

        if 'nuevo_estado' not in data:
            return jsonify({'error': 'Nuevo estado es requerido'}), 400

        nuevo_estado = data['nuevo_estado']

        resultado = admin_manager.editar_estado_servicio(id_servicio, nuevo_estado)

        if resultado:
            return jsonify({'mensaje': 'Estado del servicio editado exitosamente'}), 200
        else:
            return jsonify({'error': 'No se pudo editar el estado del servicio'}), 500

    except Exception as e:
        return jsonify({'error': str(e)}), 500

