from sqlalchemy import create_engine, text

def get_engine():
    try:
        # Configuración para XAMPP
        USER = "root"
        PASSWORD = "" # XAMPP por defecto no tiene contraseña
        HOST = "localhost"
        PORT = "3306"
        DB_NAME = "test" # Usa 'test', que viene creada por defecto en XAMPP

        # La cadena de conexión cambia a mysql+pymysql
        url = f"mysql+pymysql://{USER}:{PASSWORD}@{HOST}:{PORT}/{DB_NAME}"
        
        engine = create_engine(url)
        
        with engine.connect() as connection:
            connection.execute(text("SELECT 1"))
            print("✅ ¡Conexión exitosa a XAMPP!")
        
        return engine
    
    except Exception as e:
        print(f"❌ Error: {e}")
        return None

if __name__ == "__main__":
    get_engine()