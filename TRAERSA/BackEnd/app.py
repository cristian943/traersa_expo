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