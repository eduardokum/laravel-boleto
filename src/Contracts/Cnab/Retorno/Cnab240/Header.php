<?php
/**
 *   Copyright (c) 2016 Eduardo Gusmão
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a
 *   copy of this software and associated documentation files (the "Software"),
 *   to deal in the Software without restriction, including without limitation
 *   the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *   and/or sell copies of the Software, and to permit persons to whom the
 *   Software is furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 *   INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 *   PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *   COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *   WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
 *   IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240;

interface Header
{
    /**
     * @return string
     */
    public function getLoteServico();

    /**
     * @return string
     */
    public function getTipoRegistro();

    /**
     * @return string
     */
    public function getTipoInscricao();

    /**
     * @return string
     */
    public function getAgencia();

    /**
     * @return string
     */
    public function getAgenciaDigito();

    /**
     * @return string
     */
    public function getNomeEmpresa();

    /**
     * @return string
     */
    public function getHoraGeracao();

    /**
     * @return string
     */
    public function getNumeroSequencialArquivo();

    /**
     * @return string
     */
    public function getVersaoLayoutArquivo();

    /**
     * @return string
     */
    public function getNumeroInscricao();

    /**
     * @return string
     */
    public function getConta();

    /**
     * @return string
     */
    public function getContaDigito();

    /**
     * @return string
     */
    public function getCodigoCedente();

    /**
     * @param string $format
     *
     * @return string
     */
    public function getData($format = 'd/m/Y');

    /**
     * @return string
     */
    public function getConvenio();

    /**
     * @return int
     */
    public function getCodBanco();

    /**
     * @return int
     */
    public function getCodigoRemessaRetorno();

    /**
     * @return string
     */
    public function getNomeBanco();

    /**
     * @return array
     */
    public function toArray();

}