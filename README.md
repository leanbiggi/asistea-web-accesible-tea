# Asistea: Gestión y Asistencia para Usuarios con TEA

**Asistea** es una aplicación web diseñada para niños con TEA (7 a 14 años) y sus responsables. El proyecto fue desarrollado como **Práctica Profesional Supervisada (PPS)** en la UTN, cumpliendo con los estándares de accesibilidad **WCAG 2.1 Nivel AA**.

## 🌟 Características Principales
* **Desarrollo Fullstack:** Implementado en PHP con arquitectura modular para mejorar la mantenibilidad.
* **Accesibilidad Nativa:** Diseño de alto contraste, navegación completa por teclado y compatibilidad total con el lector de pantalla **NVDA**.
* **Gestión de Actividades:** Sistema de asignación de tareas con lógica de recurrencia relacional en base de datos.
* **Captura de Evidencias:** Integración de **Canvas API** y conversión a **Base64** para la subida de imágenes de actividades realizadas.

## 🛠️ Tecnologías y Herramientas
* **Backend:** PHP / MySQL.
* **Frontend:** HTML5 Semántico, CSS3 (Responsive Design), JavaScript.
* **Validación de Calidad:** WAVE Evaluation Tool, Color Contrast Analyzer, NVDA.

## ♿ Detalles de Accesibilidad (QA)
Siguiendo la metodología aprendida, se implementó:
* **Gestión de Foco:** Manipulación programática mediante JavaScript (`.focus()`) para asegurar que el usuario siempre sepa dónde está, especialmente en modales.
* **Semántica Robusta:** Uso de etiquetas estructurales y roles ARIA donde el HTML nativo no era suficiente.
* **Adaptabilidad:** Soporte de Zoom del 200% y 400% sin pérdida de funcionalidad (Criterio WCAG 1.4.4).

---
*Para ver el análisis detallado, consulta el [Informe Final de PPS](./docs/anexo-Informe-final-PS4.pdf).*
