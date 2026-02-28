<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * ValidarCedulaEcuatoriana
 *
 * Regla de validación personalizada de Laravel para verificar que un número
 * de cédula ecuatoriana sea estructuralmente válido, siguiendo el algoritmo
 * oficial del Registro Civil del Ecuador.
 *
 * Pasos del algoritmo (módulo 10):
 *  1. La cédula debe tener exactamente 10 dígitos.
 *  2. Los dos primeros dígitos (código de provincia) deben estar entre 01 y 24.
 *  3. El tercer dígito debe ser menor que 7 (0–6).
 *  4. Se toman los primeros 9 dígitos y se aplica el algoritmo de Luhn
 *     simplificado: los dígitos en posición impar (1ª, 3ª, 5ª...) se
 *     multiplican por 2; si el resultado supera 9, se resta 9.
 *  5. Se suman todos los dígitos resultantes.
 *  6. El dígito verificador = (suma % 10 == 0) ? 0 : 10 - (suma % 10).
 *  7. Se compara con el décimo dígito de la cédula.
 */
class ValidarCedulaEcuatoriana implements ValidationRule
{
    /**
     * Ejecuta la regla de validación.
     *
     * @param  string   $attribute Nombre del campo que se está validando.
     * @param  mixed    $value     Valor ingresado por el usuario.
     * @param  Closure  $fail      Función a llamar si la validación falla.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cedula = (string) $value;

        // 1. Debe tener exactamente 10 dígitos
        if (!preg_match('/^\d{10}$/', $cedula)) {
            $fail('La cédula debe tener exactamente 10 dígitos numéricos.');
            return;
        }

        // 2. Los primeros 2 dígitos deben estar entre 01 y 24 (provincias del Ecuador)
        $provincia = (int) substr($cedula, 0, 2);
        if ($provincia < 1 || $provincia > 24) {
            $fail('La cédula ingresada no corresponde a una provincia válida del Ecuador.');
            return;
        }

        // 3. El tercer dígito debe ser 0, 1, 2, 3, 4, 5 o 6
        $tercerDigito = (int) $cedula[2];
        if ($tercerDigito >= 7) {
            $fail('La cédula ingresada no es válida.');
            return;
        }

        // 4-6. Algoritmo de módulo 10 sobre los primeros 9 dígitos
        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $digito = (int) $cedula[$i];

            // Posiciones impares (índice par: 0, 2, 4, 6, 8) se multiplican por 2
            if ($i % 2 === 0) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }

            $suma += $digito;
        }

        // 7. Calcular y comparar el dígito verificador
        $verificador = ($suma % 10 === 0) ? 0 : (10 - ($suma % 10));

        if ($verificador !== (int) $cedula[9]) {
            $fail('La cédula ingresada no es válida. Verifica que el número sea correcto.');
        }
    }
}
