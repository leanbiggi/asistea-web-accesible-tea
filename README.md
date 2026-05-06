# Asistea: Gestión y Asistencia para Usuarios con TEA

**Asistea** es una aplicación web diseñada para niños con TEA (Trastorno del Espectro Autista) de entre 7 y 14 años y sus adultos responsables. Este proyecto fue desarrollado como **Práctica Profesional Supervisada (PPS)** en la UTN, integrando requisitos de accesibilidad **WCAG 2.1 Nivel AA** desde su fase de diseño.

## 🚀 Punto de Entrada
El archivo principal de navegación y acceso a la plataforma es:
* **[asistea.php](./src/asistea.php)** (Actúa como el index principal del proyecto).

## 🌟 Características Principales
* **Desarrollo Fullstack:** Implementado en PHP con una arquitectura modular basada en componentes para una mejor mantenibilidad.
* **Accesibilidad Nativa:** Diseño de alto contraste, navegación completa por teclado y compatibilidad certificada con el lector de pantalla **NVDA**.
* **Gestión de Actividades:** Sistema relacional para la creación de secuencias de pasos multimedia (texto, imagen y audio).
* **Integridad de Datos:** Lógica avanzada de limpieza de archivos en el servidor; al eliminar una cuenta o actividad, el sistema borra automáticamente los archivos físicos asociados (imágenes y audios) para optimizar el almacenamiento.

## 🛠️ Tecnologías y Herramientas
* **Backend:** PHP / MySQL.
* **Frontend:** HTML5 Semántico, CSS3 (Responsive Design), JavaScript.
* **Validación de Calidad:** WAVE Evaluation Tool, Color Contrast Analyzer, NVDA.

## ♿ Enfoque en Calidad y Accesibilidad (QA)
Como parte del proceso de formación en QA, se aplicaron las siguientes técnicas:
* **Gestión de Foco:** Control programático mediante JavaScript (`.focus()`) para garantizar que los anuncios de estado y modales sean percibidos correctamente.
* **Captura Multimedia:** Uso de **Canvas API** y conversión a **Base64** para la gestión de imágenes desde la cámara del dispositivo.
* **Adaptabilidad:** Verificación de soporte para zoom del 200% y 400% sin pérdida de contenido (WCAG 1.4.4).

## 📂 Estructura del Repositorio
* **[/src](./src/):** Contiene el código fuente de la aplicación.
* **[/componentes](./componentes/):** Lógica de procesamiento, incluyendo la gestión de archivos y transacciones SQL.
* **[/docs](./docs/):** Documentación técnica completa y reportes de avance de la PPS.
* **[/database](./database/):** Script SQL para la replicación del modelo de datos.

## 📄 Documentación Técnica
Para un análisis profundo del ciclo de vida del proyecto, puedes consultar los informes oficiales:
1. [Informe de Planificación y Diseño (PS2)](./docs/Informe_Formulario_PS2.docx)
2. [Informe de Avance y Desarrollo (PS3)](./docs/Informe_de_avance_PS3.docx)
3. [Informe Final de Calidad y Accesibilidad (PS4)](./docs/anexo%20Informe%20final%20-%20PS4.docx)

---
*Este proyecto refleja mi compromiso con el desarrollo de software inclusivo y de alta calidad.*
