# Asistea: Gestión y Asistencia para Usuarios con TEA

**Asistea** es una aplicación web diseñada para niños con TEA (Trastorno del Espectro Autista) de entre 7 y 14 años y sus adultos responsables. Este proyecto fue desarrollado como **Práctica Profesional Supervisada (PPS)** en la UTN, integrando requisitos de calidad y accesibilidad **WCAG 2.1 Nivel AA**.

## 🚀 Acceso al Proyecto
* **[Demo Visual (Interfaz Estática)](https://leanbiggi.github.io/asistea-web-accesible-tea/)**
  > *Nota para Reclutadores: GitHub Pages no soporta PHP/MySQL. Esta demo es una versión estática para visualizar el diseño, el contraste y la estructura accesible (WCAG 2.1 AA).*
* **[Código Fuente Completo (PHP/Backend)](./src/asistea.php)**

## 🌟 Características Principales
* **Desarrollo Fullstack:** Implementado en PHP con una arquitectura modular para facilitar el mantenimiento y la escalabilidad.
* **Accesibilidad Nativa:** Diseño de alto contraste, navegación completa por teclado y compatibilidad total con el lector de pantalla **NVDA**.
* **Gestión de Actividades:** Sistema relacional para crear secuencias de pasos multimedia (texto, imagen y audio).
* **Integridad de Datos (QA):** Lógica avanzada de limpieza en el servidor. Al eliminar una cuenta, el sistema borra automáticamente los archivos físicos asociados (imágenes y audios) para evitar "archivos huérfanos".

## 🛠️ Tecnologías y Herramientas
* **Backend:** PHP / MySQL.
* **Frontend:** HTML5 Semántico, CSS3 (Responsive Design), JavaScript.
* **Validación de Calidad:** WAVE Evaluation Tool, Color Contrast Analyzer, Lector de pantalla NVDA.

## ♿ Enfoque en Calidad y Accesibilidad (QA)
Como parte del perfil de **Tester Funcional**, se aplicaron las siguientes técnicas de verificación:
* **Gestión de Foco:** Control programático mediante JavaScript (`.focus()`) para asegurar que los anuncios de estado y modales sean percibidos por tecnologías asistivas.
* **Captura Multimedia:** Uso de **Canvas API** y conversión a **Base64** para la gestión de evidencias fotográficas.
* **Adaptabilidad:** Soporte de Zoom del 200% y 400% sin pérdida de funcionalidad (Criterio WCAG 1.4.4).

## 📂 Estructura del Repositorio
* **[/src](./src/):** Código fuente principal de la aplicación.
* **[/src/componentes](./src/componentes/):** Lógica de procesamiento y gestión de archivos.
* **[/docs](./docs/):** Informes técnicos y planes de prueba de la PPS.
* **[/database](./database/):** Script SQL para la replicación del modelo de datos.

## 📄 Documentación Técnica
Puedes consultar el análisis detallado del ciclo de vida del proyecto aquí:
1. [Informe de Planificación y Diseño (PS2)](./docs/Informe_Formulario_PS2.pdf)
2. [Informe de Avance y Desarrollo (PS3)](./docs/Informe_de_avance_PS3.pdf)
3. [Informe Final de Calidad y Accesibilidad (PS4)](./docs/Informe_final_PS4.pdf)

---
*Proyecto desarrollado por Leandro Biggi - Carrera de Ingeniería en Sistemas de Información (UTN).*
