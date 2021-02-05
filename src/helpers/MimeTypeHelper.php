<?php

namespace craft\feedme\helpers;

class MimeTypeHelper
{
    public static function getExtension($mimetype)
    {
        $mapping = self::_getMapping();
        $mimetype = self::_clean($mimetype);

        if (!empty($mapping['extensions'][$mimetype])) {
            return $mapping['extensions'][$mimetype][0];
        }

        return null;
    }

    private static function _getMapping()
    {
        return [
            'mimes' =>
                [
                    'wof' =>
                        [
                            0 => 'application/font-woff',
                        ],
                    'php' =>
                        [
                            0 => 'application/php',
                            1 => 'application/x-httpd-php',
                            2 => 'application/x-httpd-php-source',
                            3 => 'application/x-php',
                            4 => 'text/php',
                            5 => 'text/x-php',
                        ],
                    'otf' =>
                        [
                            0 => 'application/x-font-otf',
                            1 => 'font/otf',
                        ],
                    'ttf' =>
                        [
                            0 => 'application/x-font-ttf',
                            1 => 'font/ttf',
                        ],
                    'ttc' =>
                        [
                            0 => 'application/x-font-ttf',
                            1 => 'font/collection',
                        ],
                    'zip' =>
                        [
                            0 => 'application/x-gzip',
                            1 => 'application/zip',
                        ],
                    'amr' =>
                        [
                            0 => 'audio/amr',
                        ],
                    'mp3' =>
                        [
                            0 => 'audio/mpeg',
                        ],
                    'mpga' =>
                        [
                            0 => 'audio/mpeg',
                        ],
                    'mp2' =>
                        [
                            0 => 'audio/mpeg',
                        ],
                    'mp2a' =>
                        [
                            0 => 'audio/mpeg',
                        ],
                    'm2a' =>
                        [
                            0 => 'audio/mpeg',
                        ],
                    'm3a' =>
                        [
                            0 => 'audio/mpeg',
                        ],
                    'jpg' =>
                        [
                            0 => 'image/jpeg',
                        ],
                    'jpeg' =>
                        [
                            0 => 'image/jpeg',
                        ],
                    'jpe' =>
                        [
                            0 => 'image/jpeg',
                        ],
                    'bmp' =>
                        [
                            0 => 'image/x-ms-bmp',
                            1 => 'image/bmp',
                        ],
                    'ez' =>
                        [
                            0 => 'application/andrew-inset',
                        ],
                    'aw' =>
                        [
                            0 => 'application/applixware',
                        ],
                    'atom' =>
                        [
                            0 => 'application/atom+xml',
                        ],
                    'atomcat' =>
                        [
                            0 => 'application/atomcat+xml',
                        ],
                    'atomsvc' =>
                        [
                            0 => 'application/atomsvc+xml',
                        ],
                    'ccxml' =>
                        [
                            0 => 'application/ccxml+xml',
                        ],
                    'cdmia' =>
                        [
                            0 => 'application/cdmi-capability',
                        ],
                    'cdmic' =>
                        [
                            0 => 'application/cdmi-container',
                        ],
                    'cdmid' =>
                        [
                            0 => 'application/cdmi-domain',
                        ],
                    'cdmio' =>
                        [
                            0 => 'application/cdmi-object',
                        ],
                    'cdmiq' =>
                        [
                            0 => 'application/cdmi-queue',
                        ],
                    'cu' =>
                        [
                            0 => 'application/cu-seeme',
                        ],
                    'davmount' =>
                        [
                            0 => 'application/davmount+xml',
                        ],
                    'dbk' =>
                        [
                            0 => 'application/docbook+xml',
                        ],
                    'dssc' =>
                        [
                            0 => 'application/dssc+der',
                        ],
                    'xdssc' =>
                        [
                            0 => 'application/dssc+xml',
                        ],
                    'ecma' =>
                        [
                            0 => 'application/ecmascript',
                        ],
                    'emma' =>
                        [
                            0 => 'application/emma+xml',
                        ],
                    'epub' =>
                        [
                            0 => 'application/epub+zip',
                        ],
                    'exi' =>
                        [
                            0 => 'application/exi',
                        ],
                    'pfr' =>
                        [
                            0 => 'application/font-tdpfr',
                        ],
                    'gml' =>
                        [
                            0 => 'application/gml+xml',
                        ],
                    'gpx' =>
                        [
                            0 => 'application/gpx+xml',
                        ],
                    'gxf' =>
                        [
                            0 => 'application/gxf',
                        ],
                    'stk' =>
                        [
                            0 => 'application/hyperstudio',
                        ],
                    'ink' =>
                        [
                            0 => 'application/inkml+xml',
                        ],
                    'inkml' =>
                        [
                            0 => 'application/inkml+xml',
                        ],
                    'ipfix' =>
                        [
                            0 => 'application/ipfix',
                        ],
                    'jar' =>
                        [
                            0 => 'application/java-archive',
                        ],
                    'ser' =>
                        [
                            0 => 'application/java-serialized-object',
                        ],
                    'class' =>
                        [
                            0 => 'application/java-vm',
                        ],
                    'js' =>
                        [
                            0 => 'application/javascript',
                        ],
                    'json' =>
                        [
                            0 => 'application/json',
                        ],
                    'jsonml' =>
                        [
                            0 => 'application/jsonml+json',
                        ],
                    'lostxml' =>
                        [
                            0 => 'application/lost+xml',
                        ],
                    'hqx' =>
                        [
                            0 => 'application/mac-binhex40',
                        ],
                    'cpt' =>
                        [
                            0 => 'application/mac-compactpro',
                        ],
                    'mads' =>
                        [
                            0 => 'application/mads+xml',
                        ],
                    'mrc' =>
                        [
                            0 => 'application/marc',
                        ],
                    'mrcx' =>
                        [
                            0 => 'application/marcxml+xml',
                        ],
                    'ma' =>
                        [
                            0 => 'application/mathematica',
                        ],
                    'nb' =>
                        [
                            0 => 'application/mathematica',
                        ],
                    'mb' =>
                        [
                            0 => 'application/mathematica',
                        ],
                    'mathml' =>
                        [
                            0 => 'application/mathml+xml',
                        ],
                    'mbox' =>
                        [
                            0 => 'application/mbox',
                        ],
                    'mscml' =>
                        [
                            0 => 'application/mediaservercontrol+xml',
                        ],
                    'metalink' =>
                        [
                            0 => 'application/metalink+xml',
                        ],
                    'meta4' =>
                        [
                            0 => 'application/metalink4+xml',
                        ],
                    'mets' =>
                        [
                            0 => 'application/mets+xml',
                        ],
                    'mods' =>
                        [
                            0 => 'application/mods+xml',
                        ],
                    'm21' =>
                        [
                            0 => 'application/mp21',
                        ],
                    'mp21' =>
                        [
                            0 => 'application/mp21',
                        ],
                    'mp4s' =>
                        [
                            0 => 'application/mp4',
                        ],
                    'doc' =>
                        [
                            0 => 'application/msword',
                        ],
                    'dot' =>
                        [
                            0 => 'application/msword',
                        ],
                    'mxf' =>
                        [
                            0 => 'application/mxf',
                        ],
                    'bin' =>
                        [
                            0 => 'application/octet-stream',
                        ],
                    'dms' =>
                        [
                            0 => 'application/octet-stream',
                        ],
                    'lrf' =>
                        [
                            0 => 'application/octet-stream',
                        ],
                    'mar' =>
                        [
                            0 => 'application/octet-stream',
                        ],
                    'so' =>
                        [
                            0 => 'application/octet-stream',
                        ],
                    'dist' =>
                        [
                            0 => 'application/octet-stream',
                        ],
                    'distz' =>
                        [
                            0 => 'application/octet-stream',
                        ],
                    'pkg' =>
                        [
                            0 => 'application/octet-stream',
                        ],
                    'bpk' =>
                        [
                            0 => 'application/octet-stream',
                        ],
                    'dump' =>
                        [
                            0 => 'application/octet-stream',
                        ],
                    'elc' =>
                        [
                            0 => 'application/octet-stream',
                        ],
                    'deploy' =>
                        [
                            0 => 'application/octet-stream',
                        ],
                    'oda' =>
                        [
                            0 => 'application/oda',
                        ],
                    'opf' =>
                        [
                            0 => 'application/oebps-package+xml',
                        ],
                    'ogx' =>
                        [
                            0 => 'application/ogg',
                        ],
                    'omdoc' =>
                        [
                            0 => 'application/omdoc+xml',
                        ],
                    'onetoc' =>
                        [
                            0 => 'application/onenote',
                        ],
                    'onetoc2' =>
                        [
                            0 => 'application/onenote',
                        ],
                    'onetmp' =>
                        [
                            0 => 'application/onenote',
                        ],
                    'onepkg' =>
                        [
                            0 => 'application/onenote',
                        ],
                    'oxps' =>
                        [
                            0 => 'application/oxps',
                        ],
                    'xer' =>
                        [
                            0 => 'application/patch-ops-error+xml',
                        ],
                    'pdf' =>
                        [
                            0 => 'application/pdf',
                        ],
                    'pgp' =>
                        [
                            0 => 'application/pgp-encrypted',
                        ],
                    'asc' =>
                        [
                            0 => 'application/pgp-signature',
                        ],
                    'sig' =>
                        [
                            0 => 'application/pgp-signature',
                        ],
                    'prf' =>
                        [
                            0 => 'application/pics-rules',
                        ],
                    'p10' =>
                        [
                            0 => 'application/pkcs10',
                        ],
                    'p7m' =>
                        [
                            0 => 'application/pkcs7-mime',
                        ],
                    'p7c' =>
                        [
                            0 => 'application/pkcs7-mime',
                        ],
                    'p7s' =>
                        [
                            0 => 'application/pkcs7-signature',
                        ],
                    'p8' =>
                        [
                            0 => 'application/pkcs8',
                        ],
                    'ac' =>
                        [
                            0 => 'application/pkix-attr-cert',
                        ],
                    'cer' =>
                        [
                            0 => 'application/pkix-cert',
                        ],
                    'crl' =>
                        [
                            0 => 'application/pkix-crl',
                        ],
                    'pkipath' =>
                        [
                            0 => 'application/pkix-pkipath',
                        ],
                    'pki' =>
                        [
                            0 => 'application/pkixcmp',
                        ],
                    'pls' =>
                        [
                            0 => 'application/pls+xml',
                        ],
                    'ai' =>
                        [
                            0 => 'application/postscript',
                        ],
                    'eps' =>
                        [
                            0 => 'application/postscript',
                        ],
                    'ps' =>
                        [
                            0 => 'application/postscript',
                        ],
                    'cww' =>
                        [
                            0 => 'application/prs.cww',
                        ],
                    'pskcxml' =>
                        [
                            0 => 'application/pskc+xml',
                        ],
                    'rdf' =>
                        [
                            0 => 'application/rdf+xml',
                        ],
                    'rif' =>
                        [
                            0 => 'application/reginfo+xml',
                        ],
                    'rnc' =>
                        [
                            0 => 'application/relax-ng-compact-syntax',
                        ],
                    'rl' =>
                        [
                            0 => 'application/resource-lists+xml',
                        ],
                    'rld' =>
                        [
                            0 => 'application/resource-lists-diff+xml',
                        ],
                    'rs' =>
                        [
                            0 => 'application/rls-services+xml',
                        ],
                    'gbr' =>
                        [
                            0 => 'application/rpki-ghostbusters',
                        ],
                    'mft' =>
                        [
                            0 => 'application/rpki-manifest',
                        ],
                    'roa' =>
                        [
                            0 => 'application/rpki-roa',
                        ],
                    'rsd' =>
                        [
                            0 => 'application/rsd+xml',
                        ],
                    'rss' =>
                        [
                            0 => 'application/rss+xml',
                        ],
                    'rtf' =>
                        [
                            0 => 'application/rtf',
                        ],
                    'sbml' =>
                        [
                            0 => 'application/sbml+xml',
                        ],
                    'scq' =>
                        [
                            0 => 'application/scvp-cv-request',
                        ],
                    'scs' =>
                        [
                            0 => 'application/scvp-cv-response',
                        ],
                    'spq' =>
                        [
                            0 => 'application/scvp-vp-request',
                        ],
                    'spp' =>
                        [
                            0 => 'application/scvp-vp-response',
                        ],
                    'sdp' =>
                        [
                            0 => 'application/sdp',
                        ],
                    'setpay' =>
                        [
                            0 => 'application/set-payment-initiation',
                        ],
                    'setreg' =>
                        [
                            0 => 'application/set-registration-initiation',
                        ],
                    'shf' =>
                        [
                            0 => 'application/shf+xml',
                        ],
                    'smi' =>
                        [
                            0 => 'application/smil+xml',
                        ],
                    'smil' =>
                        [
                            0 => 'application/smil+xml',
                        ],
                    'rq' =>
                        [
                            0 => 'application/sparql-query',
                        ],
                    'srx' =>
                        [
                            0 => 'application/sparql-results+xml',
                        ],
                    'gram' =>
                        [
                            0 => 'application/srgs',
                        ],
                    'grxml' =>
                        [
                            0 => 'application/srgs+xml',
                        ],
                    'sru' =>
                        [
                            0 => 'application/sru+xml',
                        ],
                    'ssdl' =>
                        [
                            0 => 'application/ssdl+xml',
                        ],
                    'ssml' =>
                        [
                            0 => 'application/ssml+xml',
                        ],
                    'tei' =>
                        [
                            0 => 'application/tei+xml',
                        ],
                    'teicorpus' =>
                        [
                            0 => 'application/tei+xml',
                        ],
                    'tfi' =>
                        [
                            0 => 'application/thraud+xml',
                        ],
                    'tsd' =>
                        [
                            0 => 'application/timestamped-data',
                        ],
                    'plb' =>
                        [
                            0 => 'application/vnd.3gpp.pic-bw-large',
                        ],
                    'psb' =>
                        [
                            0 => 'application/vnd.3gpp.pic-bw-small',
                        ],
                    'pvb' =>
                        [
                            0 => 'application/vnd.3gpp.pic-bw-var',
                        ],
                    'tcap' =>
                        [
                            0 => 'application/vnd.3gpp2.tcap',
                        ],
                    'pwn' =>
                        [
                            0 => 'application/vnd.3m.post-it-notes',
                        ],
                    'aso' =>
                        [
                            0 => 'application/vnd.accpac.simply.aso',
                        ],
                    'imp' =>
                        [
                            0 => 'application/vnd.accpac.simply.imp',
                        ],
                    'acu' =>
                        [
                            0 => 'application/vnd.acucobol',
                        ],
                    'atc' =>
                        [
                            0 => 'application/vnd.acucorp',
                        ],
                    'acutc' =>
                        [
                            0 => 'application/vnd.acucorp',
                        ],
                    'air' =>
                        [
                            0 => 'application/vnd.adobe.air-application-installer-package+zip',
                        ],
                    'fcdt' =>
                        [
                            0 => 'application/vnd.adobe.formscentral.fcdt',
                        ],
                    'fxp' =>
                        [
                            0 => 'application/vnd.adobe.fxp',
                        ],
                    'fxpl' =>
                        [
                            0 => 'application/vnd.adobe.fxp',
                        ],
                    'xdp' =>
                        [
                            0 => 'application/vnd.adobe.xdp+xml',
                        ],
                    'xfdf' =>
                        [
                            0 => 'application/vnd.adobe.xfdf',
                        ],
                    'ahead' =>
                        [
                            0 => 'application/vnd.ahead.space',
                        ],
                    'azf' =>
                        [
                            0 => 'application/vnd.airzip.filesecure.azf',
                        ],
                    'azs' =>
                        [
                            0 => 'application/vnd.airzip.filesecure.azs',
                        ],
                    'azw' =>
                        [
                            0 => 'application/vnd.amazon.ebook',
                        ],
                    'acc' =>
                        [
                            0 => 'application/vnd.americandynamics.acc',
                        ],
                    'ami' =>
                        [
                            0 => 'application/vnd.amiga.ami',
                        ],
                    'apk' =>
                        [
                            0 => 'application/vnd.android.package-archive',
                        ],
                    'cii' =>
                        [
                            0 => 'application/vnd.anser-web-certificate-issue-initiation',
                        ],
                    'fti' =>
                        [
                            0 => 'application/vnd.anser-web-funds-transfer-initiation',
                        ],
                    'atx' =>
                        [
                            0 => 'application/vnd.antix.game-component',
                        ],
                    'mpkg' =>
                        [
                            0 => 'application/vnd.apple.installer+xml',
                        ],
                    'm3u8' =>
                        [
                            0 => 'application/vnd.apple.mpegurl',
                        ],
                    'swi' =>
                        [
                            0 => 'application/vnd.aristanetworks.swi',
                        ],
                    'iota' =>
                        [
                            0 => 'application/vnd.astraea-software.iota',
                        ],
                    'aep' =>
                        [
                            0 => 'application/vnd.audiograph',
                        ],
                    'mpm' =>
                        [
                            0 => 'application/vnd.blueice.multipass',
                        ],
                    'bmi' =>
                        [
                            0 => 'application/vnd.bmi',
                        ],
                    'rep' =>
                        [
                            0 => 'application/vnd.businessobjects',
                        ],
                    'cdxml' =>
                        [
                            0 => 'application/vnd.chemdraw+xml',
                        ],
                    'mmd' =>
                        [
                            0 => 'application/vnd.chipnuts.karaoke-mmd',
                        ],
                    'cdy' =>
                        [
                            0 => 'application/vnd.cinderella',
                        ],
                    'cla' =>
                        [
                            0 => 'application/vnd.claymore',
                        ],
                    'rp9' =>
                        [
                            0 => 'application/vnd.cloanto.rp9',
                        ],
                    'c4g' =>
                        [
                            0 => 'application/vnd.clonk.c4group',
                        ],
                    'c4d' =>
                        [
                            0 => 'application/vnd.clonk.c4group',
                        ],
                    'c4f' =>
                        [
                            0 => 'application/vnd.clonk.c4group',
                        ],
                    'c4p' =>
                        [
                            0 => 'application/vnd.clonk.c4group',
                        ],
                    'c4u' =>
                        [
                            0 => 'application/vnd.clonk.c4group',
                        ],
                    'c11amc' =>
                        [
                            0 => 'application/vnd.cluetrust.cartomobile-config',
                        ],
                    'c11amz' =>
                        [
                            0 => 'application/vnd.cluetrust.cartomobile-config-pkg',
                        ],
                    'csp' =>
                        [
                            0 => 'application/vnd.commonspace',
                        ],
                    'cdbcmsg' =>
                        [
                            0 => 'application/vnd.contact.cmsg',
                        ],
                    'cmc' =>
                        [
                            0 => 'application/vnd.cosmocaller',
                        ],
                    'clkx' =>
                        [
                            0 => 'application/vnd.crick.clicker',
                        ],
                    'clkk' =>
                        [
                            0 => 'application/vnd.crick.clicker.keyboard',
                        ],
                    'clkp' =>
                        [
                            0 => 'application/vnd.crick.clicker.palette',
                        ],
                    'clkt' =>
                        [
                            0 => 'application/vnd.crick.clicker.template',
                        ],
                    'clkw' =>
                        [
                            0 => 'application/vnd.crick.clicker.wordbank',
                        ],
                    'wbs' =>
                        [
                            0 => 'application/vnd.criticaltools.wbs+xml',
                        ],
                    'pml' =>
                        [
                            0 => 'application/vnd.ctc-posml',
                        ],
                    'ppd' =>
                        [
                            0 => 'application/vnd.cups-ppd',
                        ],
                    'car' =>
                        [
                            0 => 'application/vnd.curl.car',
                        ],
                    'pcurl' =>
                        [
                            0 => 'application/vnd.curl.pcurl',
                        ],
                    'dart' =>
                        [
                            0 => 'application/vnd.dart',
                        ],
                    'rdz' =>
                        [
                            0 => 'application/vnd.data-vision.rdz',
                        ],
                    'uvf' =>
                        [
                            0 => 'application/vnd.dece.data',
                        ],
                    'uvvf' =>
                        [
                            0 => 'application/vnd.dece.data',
                        ],
                    'uvd' =>
                        [
                            0 => 'application/vnd.dece.data',
                        ],
                    'uvvd' =>
                        [
                            0 => 'application/vnd.dece.data',
                        ],
                    'uvt' =>
                        [
                            0 => 'application/vnd.dece.ttml+xml',
                        ],
                    'uvvt' =>
                        [
                            0 => 'application/vnd.dece.ttml+xml',
                        ],
                    'uvx' =>
                        [
                            0 => 'application/vnd.dece.unspecified',
                        ],
                    'uvvx' =>
                        [
                            0 => 'application/vnd.dece.unspecified',
                        ],
                    'uvz' =>
                        [
                            0 => 'application/vnd.dece.zip',
                        ],
                    'uvvz' =>
                        [
                            0 => 'application/vnd.dece.zip',
                        ],
                    'fe_launch' =>
                        [
                            0 => 'application/vnd.denovo.fcselayout-link',
                        ],
                    'dna' =>
                        [
                            0 => 'application/vnd.dna',
                        ],
                    'mlp' =>
                        [
                            0 => 'application/vnd.dolby.mlp',
                        ],
                    'dpg' =>
                        [
                            0 => 'application/vnd.dpgraph',
                        ],
                    'dfac' =>
                        [
                            0 => 'application/vnd.dreamfactory',
                        ],
                    'kpxx' =>
                        [
                            0 => 'application/vnd.ds-keypoint',
                        ],
                    'ait' =>
                        [
                            0 => 'application/vnd.dvb.ait',
                        ],
                    'svc' =>
                        [
                            0 => 'application/vnd.dvb.service',
                        ],
                    'geo' =>
                        [
                            0 => 'application/vnd.dynageo',
                        ],
                    'mag' =>
                        [
                            0 => 'application/vnd.ecowin.chart',
                        ],
                    'nml' =>
                        [
                            0 => 'application/vnd.enliven',
                        ],
                    'esf' =>
                        [
                            0 => 'application/vnd.epson.esf',
                        ],
                    'msf' =>
                        [
                            0 => 'application/vnd.epson.msf',
                        ],
                    'qam' =>
                        [
                            0 => 'application/vnd.epson.quickanime',
                        ],
                    'slt' =>
                        [
                            0 => 'application/vnd.epson.salt',
                        ],
                    'ssf' =>
                        [
                            0 => 'application/vnd.epson.ssf',
                        ],
                    'es3' =>
                        [
                            0 => 'application/vnd.eszigno3+xml',
                        ],
                    'et3' =>
                        [
                            0 => 'application/vnd.eszigno3+xml',
                        ],
                    'ez2' =>
                        [
                            0 => 'application/vnd.ezpix-album',
                        ],
                    'ez3' =>
                        [
                            0 => 'application/vnd.ezpix-package',
                        ],
                    'fdf' =>
                        [
                            0 => 'application/vnd.fdf',
                        ],
                    'mseed' =>
                        [
                            0 => 'application/vnd.fdsn.mseed',
                        ],
                    'seed' =>
                        [
                            0 => 'application/vnd.fdsn.seed',
                        ],
                    'dataless' =>
                        [
                            0 => 'application/vnd.fdsn.seed',
                        ],
                    'gph' =>
                        [
                            0 => 'application/vnd.flographit',
                        ],
                    'ftc' =>
                        [
                            0 => 'application/vnd.fluxtime.clip',
                        ],
                    'fm' =>
                        [
                            0 => 'application/vnd.framemaker',
                        ],
                    'frame' =>
                        [
                            0 => 'application/vnd.framemaker',
                        ],
                    'maker' =>
                        [
                            0 => 'application/vnd.framemaker',
                        ],
                    'book' =>
                        [
                            0 => 'application/vnd.framemaker',
                        ],
                    'fnc' =>
                        [
                            0 => 'application/vnd.frogans.fnc',
                        ],
                    'ltf' =>
                        [
                            0 => 'application/vnd.frogans.ltf',
                        ],
                    'fsc' =>
                        [
                            0 => 'application/vnd.fsc.weblaunch',
                        ],
                    'oas' =>
                        [
                            0 => 'application/vnd.fujitsu.oasys',
                        ],
                    'oa2' =>
                        [
                            0 => 'application/vnd.fujitsu.oasys2',
                        ],
                    'oa3' =>
                        [
                            0 => 'application/vnd.fujitsu.oasys3',
                        ],
                    'fg5' =>
                        [
                            0 => 'application/vnd.fujitsu.oasysgp',
                        ],
                    'bh2' =>
                        [
                            0 => 'application/vnd.fujitsu.oasysprs',
                        ],
                    'ddd' =>
                        [
                            0 => 'application/vnd.fujixerox.ddd',
                        ],
                    'xdw' =>
                        [
                            0 => 'application/vnd.fujixerox.docuworks',
                        ],
                    'xbd' =>
                        [
                            0 => 'application/vnd.fujixerox.docuworks.binder',
                        ],
                    'fzs' =>
                        [
                            0 => 'application/vnd.fuzzysheet',
                        ],
                    'txd' =>
                        [
                            0 => 'application/vnd.genomatix.tuxedo',
                        ],
                    'ggb' =>
                        [
                            0 => 'application/vnd.geogebra.file',
                        ],
                    'ggt' =>
                        [
                            0 => 'application/vnd.geogebra.tool',
                        ],
                    'gex' =>
                        [
                            0 => 'application/vnd.geometry-explorer',
                        ],
                    'gre' =>
                        [
                            0 => 'application/vnd.geometry-explorer',
                        ],
                    'gxt' =>
                        [
                            0 => 'application/vnd.geonext',
                        ],
                    'g2w' =>
                        [
                            0 => 'application/vnd.geoplan',
                        ],
                    'g3w' =>
                        [
                            0 => 'application/vnd.geospace',
                        ],
                    'gmx' =>
                        [
                            0 => 'application/vnd.gmx',
                        ],
                    'kml' =>
                        [
                            0 => 'application/vnd.google-earth.kml+xml',
                        ],
                    'kmz' =>
                        [
                            0 => 'application/vnd.google-earth.kmz',
                        ],
                    'gqf' =>
                        [
                            0 => 'application/vnd.grafeq',
                        ],
                    'gqs' =>
                        [
                            0 => 'application/vnd.grafeq',
                        ],
                    'gac' =>
                        [
                            0 => 'application/vnd.groove-account',
                        ],
                    'ghf' =>
                        [
                            0 => 'application/vnd.groove-help',
                        ],
                    'gim' =>
                        [
                            0 => 'application/vnd.groove-identity-message',
                        ],
                    'grv' =>
                        [
                            0 => 'application/vnd.groove-injector',
                        ],
                    'gtm' =>
                        [
                            0 => 'application/vnd.groove-tool-message',
                        ],
                    'tpl' =>
                        [
                            0 => 'application/vnd.groove-tool-template',
                        ],
                    'vcg' =>
                        [
                            0 => 'application/vnd.groove-vcard',
                        ],
                    'hal' =>
                        [
                            0 => 'application/vnd.hal+xml',
                        ],
                    'zmm' =>
                        [
                            0 => 'application/vnd.handheld-entertainment+xml',
                        ],
                    'hbci' =>
                        [
                            0 => 'application/vnd.hbci',
                        ],
                    'les' =>
                        [
                            0 => 'application/vnd.hhe.lesson-player',
                        ],
                    'hpgl' =>
                        [
                            0 => 'application/vnd.hp-hpgl',
                        ],
                    'hpid' =>
                        [
                            0 => 'application/vnd.hp-hpid',
                        ],
                    'hps' =>
                        [
                            0 => 'application/vnd.hp-hps',
                        ],
                    'jlt' =>
                        [
                            0 => 'application/vnd.hp-jlyt',
                        ],
                    'pcl' =>
                        [
                            0 => 'application/vnd.hp-pcl',
                        ],
                    'pclxl' =>
                        [
                            0 => 'application/vnd.hp-pclxl',
                        ],
                    'sfd-hdstx' =>
                        [
                            0 => 'application/vnd.hydrostatix.sof-data',
                        ],
                    'mpy' =>
                        [
                            0 => 'application/vnd.ibm.minipay',
                        ],
                    'afp' =>
                        [
                            0 => 'application/vnd.ibm.modcap',
                        ],
                    'listafp' =>
                        [
                            0 => 'application/vnd.ibm.modcap',
                        ],
                    'list3820' =>
                        [
                            0 => 'application/vnd.ibm.modcap',
                        ],
                    'irm' =>
                        [
                            0 => 'application/vnd.ibm.rights-management',
                        ],
                    'sc' =>
                        [
                            0 => 'application/vnd.ibm.secure-container',
                        ],
                    'icc' =>
                        [
                            0 => 'application/vnd.iccprofile',
                        ],
                    'icm' =>
                        [
                            0 => 'application/vnd.iccprofile',
                        ],
                    'igl' =>
                        [
                            0 => 'application/vnd.igloader',
                        ],
                    'ivp' =>
                        [
                            0 => 'application/vnd.immervision-ivp',
                        ],
                    'ivu' =>
                        [
                            0 => 'application/vnd.immervision-ivu',
                        ],
                    'igm' =>
                        [
                            0 => 'application/vnd.insors.igm',
                        ],
                    'xpw' =>
                        [
                            0 => 'application/vnd.intercon.formnet',
                        ],
                    'xpx' =>
                        [
                            0 => 'application/vnd.intercon.formnet',
                        ],
                    'i2g' =>
                        [
                            0 => 'application/vnd.intergeo',
                        ],
                    'qbo' =>
                        [
                            0 => 'application/vnd.intu.qbo',
                        ],
                    'qfx' =>
                        [
                            0 => 'application/vnd.intu.qfx',
                        ],
                    'rcprofile' =>
                        [
                            0 => 'application/vnd.ipunplugged.rcprofile',
                        ],
                    'irp' =>
                        [
                            0 => 'application/vnd.irepository.package+xml',
                        ],
                    'xpr' =>
                        [
                            0 => 'application/vnd.is-xpr',
                        ],
                    'fcs' =>
                        [
                            0 => 'application/vnd.isac.fcs',
                        ],
                    'jam' =>
                        [
                            0 => 'application/vnd.jam',
                        ],
                    'rms' =>
                        [
                            0 => 'application/vnd.jcp.javame.midlet-rms',
                        ],
                    'jisp' =>
                        [
                            0 => 'application/vnd.jisp',
                        ],
                    'joda' =>
                        [
                            0 => 'application/vnd.joost.joda-archive',
                        ],
                    'ktz' =>
                        [
                            0 => 'application/vnd.kahootz',
                        ],
                    'ktr' =>
                        [
                            0 => 'application/vnd.kahootz',
                        ],
                    'karbon' =>
                        [
                            0 => 'application/vnd.kde.karbon',
                        ],
                    'chrt' =>
                        [
                            0 => 'application/vnd.kde.kchart',
                        ],
                    'kfo' =>
                        [
                            0 => 'application/vnd.kde.kformula',
                        ],
                    'flw' =>
                        [
                            0 => 'application/vnd.kde.kivio',
                        ],
                    'kon' =>
                        [
                            0 => 'application/vnd.kde.kontour',
                        ],
                    'kpr' =>
                        [
                            0 => 'application/vnd.kde.kpresenter',
                        ],
                    'kpt' =>
                        [
                            0 => 'application/vnd.kde.kpresenter',
                        ],
                    'ksp' =>
                        [
                            0 => 'application/vnd.kde.kspread',
                        ],
                    'kwd' =>
                        [
                            0 => 'application/vnd.kde.kword',
                        ],
                    'kwt' =>
                        [
                            0 => 'application/vnd.kde.kword',
                        ],
                    'htke' =>
                        [
                            0 => 'application/vnd.kenameaapp',
                        ],
                    'kia' =>
                        [
                            0 => 'application/vnd.kidspiration',
                        ],
                    'kne' =>
                        [
                            0 => 'application/vnd.kinar',
                        ],
                    'knp' =>
                        [
                            0 => 'application/vnd.kinar',
                        ],
                    'skp' =>
                        [
                            0 => 'application/vnd.koan',
                        ],
                    'skd' =>
                        [
                            0 => 'application/vnd.koan',
                        ],
                    'skt' =>
                        [
                            0 => 'application/vnd.koan',
                        ],
                    'skm' =>
                        [
                            0 => 'application/vnd.koan',
                        ],
                    'sse' =>
                        [
                            0 => 'application/vnd.kodak-descriptor',
                        ],
                    'lasxml' =>
                        [
                            0 => 'application/vnd.las.las+xml',
                        ],
                    'lbd' =>
                        [
                            0 => 'application/vnd.llamagraphics.life-balance.desktop',
                        ],
                    'lbe' =>
                        [
                            0 => 'application/vnd.llamagraphics.life-balance.exchange+xml',
                        ],
                    123 =>
                        [
                            0 => 'application/vnd.lotus-1-2-3',
                        ],
                    'apr' =>
                        [
                            0 => 'application/vnd.lotus-approach',
                        ],
                    'pre' =>
                        [
                            0 => 'application/vnd.lotus-freelance',
                        ],
                    'nsf' =>
                        [
                            0 => 'application/vnd.lotus-notes',
                        ],
                    'org' =>
                        [
                            0 => 'application/vnd.lotus-organizer',
                        ],
                    'scm' =>
                        [
                            0 => 'application/vnd.lotus-screencam',
                        ],
                    'lwp' =>
                        [
                            0 => 'application/vnd.lotus-wordpro',
                        ],
                    'portpkg' =>
                        [
                            0 => 'application/vnd.macports.portpkg',
                        ],
                    'mcd' =>
                        [
                            0 => 'application/vnd.mcd',
                        ],
                    'mc1' =>
                        [
                            0 => 'application/vnd.medcalcdata',
                        ],
                    'cdkey' =>
                        [
                            0 => 'application/vnd.mediastation.cdkey',
                        ],
                    'mwf' =>
                        [
                            0 => 'application/vnd.mfer',
                        ],
                    'mfm' =>
                        [
                            0 => 'application/vnd.mfmp',
                        ],
                    'flo' =>
                        [
                            0 => 'application/vnd.micrografx.flo',
                        ],
                    'igx' =>
                        [
                            0 => 'application/vnd.micrografx.igx',
                        ],
                    'mif' =>
                        [
                            0 => 'application/vnd.mif',
                        ],
                    'daf' =>
                        [
                            0 => 'application/vnd.mobius.daf',
                        ],
                    'dis' =>
                        [
                            0 => 'application/vnd.mobius.dis',
                        ],
                    'mbk' =>
                        [
                            0 => 'application/vnd.mobius.mbk',
                        ],
                    'mqy' =>
                        [
                            0 => 'application/vnd.mobius.mqy',
                        ],
                    'msl' =>
                        [
                            0 => 'application/vnd.mobius.msl',
                        ],
                    'plc' =>
                        [
                            0 => 'application/vnd.mobius.plc',
                        ],
                    'txf' =>
                        [
                            0 => 'application/vnd.mobius.txf',
                        ],
                    'mpn' =>
                        [
                            0 => 'application/vnd.mophun.application',
                        ],
                    'mpc' =>
                        [
                            0 => 'application/vnd.mophun.certificate',
                        ],
                    'xul' =>
                        [
                            0 => 'application/vnd.mozilla.xul+xml',
                        ],
                    'cil' =>
                        [
                            0 => 'application/vnd.ms-artgalry',
                        ],
                    'cab' =>
                        [
                            0 => 'application/vnd.ms-cab-compressed',
                        ],
                    'xls' =>
                        [
                            0 => 'application/vnd.ms-excel',
                        ],
                    'xlm' =>
                        [
                            0 => 'application/vnd.ms-excel',
                        ],
                    'xla' =>
                        [
                            0 => 'application/vnd.ms-excel',
                        ],
                    'xlc' =>
                        [
                            0 => 'application/vnd.ms-excel',
                        ],
                    'xlt' =>
                        [
                            0 => 'application/vnd.ms-excel',
                        ],
                    'xlw' =>
                        [
                            0 => 'application/vnd.ms-excel',
                        ],
                    'xlam' =>
                        [
                            0 => 'application/vnd.ms-excel.addin.macroenabled.12',
                        ],
                    'xlsb' =>
                        [
                            0 => 'application/vnd.ms-excel.sheet.binary.macroenabled.12',
                        ],
                    'xlsm' =>
                        [
                            0 => 'application/vnd.ms-excel.sheet.macroenabled.12',
                        ],
                    'xltm' =>
                        [
                            0 => 'application/vnd.ms-excel.template.macroenabled.12',
                        ],
                    'eot' =>
                        [
                            0 => 'application/vnd.ms-fontobject',
                        ],
                    'chm' =>
                        [
                            0 => 'application/vnd.ms-htmlhelp',
                        ],
                    'ims' =>
                        [
                            0 => 'application/vnd.ms-ims',
                        ],
                    'lrm' =>
                        [
                            0 => 'application/vnd.ms-lrm',
                        ],
                    'thmx' =>
                        [
                            0 => 'application/vnd.ms-officetheme',
                        ],
                    'cat' =>
                        [
                            0 => 'application/vnd.ms-pki.seccat',
                        ],
                    'stl' =>
                        [
                            0 => 'application/vnd.ms-pki.stl',
                        ],
                    'ppt' =>
                        [
                            0 => 'application/vnd.ms-powerpoint',
                        ],
                    'pps' =>
                        [
                            0 => 'application/vnd.ms-powerpoint',
                        ],
                    'pot' =>
                        [
                            0 => 'application/vnd.ms-powerpoint',
                        ],
                    'ppam' =>
                        [
                            0 => 'application/vnd.ms-powerpoint.addin.macroenabled.12',
                        ],
                    'pptm' =>
                        [
                            0 => 'application/vnd.ms-powerpoint.presentation.macroenabled.12',
                        ],
                    'sldm' =>
                        [
                            0 => 'application/vnd.ms-powerpoint.slide.macroenabled.12',
                        ],
                    'ppsm' =>
                        [
                            0 => 'application/vnd.ms-powerpoint.slideshow.macroenabled.12',
                        ],
                    'potm' =>
                        [
                            0 => 'application/vnd.ms-powerpoint.template.macroenabled.12',
                        ],
                    'mpp' =>
                        [
                            0 => 'application/vnd.ms-project',
                        ],
                    'mpt' =>
                        [
                            0 => 'application/vnd.ms-project',
                        ],
                    'docm' =>
                        [
                            0 => 'application/vnd.ms-word.document.macroenabled.12',
                        ],
                    'dotm' =>
                        [
                            0 => 'application/vnd.ms-word.template.macroenabled.12',
                        ],
                    'wps' =>
                        [
                            0 => 'application/vnd.ms-works',
                        ],
                    'wks' =>
                        [
                            0 => 'application/vnd.ms-works',
                        ],
                    'wcm' =>
                        [
                            0 => 'application/vnd.ms-works',
                        ],
                    'wdb' =>
                        [
                            0 => 'application/vnd.ms-works',
                        ],
                    'wpl' =>
                        [
                            0 => 'application/vnd.ms-wpl',
                        ],
                    'xps' =>
                        [
                            0 => 'application/vnd.ms-xpsdocument',
                        ],
                    'mseq' =>
                        [
                            0 => 'application/vnd.mseq',
                        ],
                    'mus' =>
                        [
                            0 => 'application/vnd.musician',
                        ],
                    'msty' =>
                        [
                            0 => 'application/vnd.muvee.style',
                        ],
                    'taglet' =>
                        [
                            0 => 'application/vnd.mynfc',
                        ],
                    'nlu' =>
                        [
                            0 => 'application/vnd.neurolanguage.nlu',
                        ],
                    'ntf' =>
                        [
                            0 => 'application/vnd.nitf',
                        ],
                    'nitf' =>
                        [
                            0 => 'application/vnd.nitf',
                        ],
                    'nnd' =>
                        [
                            0 => 'application/vnd.noblenet-directory',
                        ],
                    'nns' =>
                        [
                            0 => 'application/vnd.noblenet-sealer',
                        ],
                    'nnw' =>
                        [
                            0 => 'application/vnd.noblenet-web',
                        ],
                    'ngdat' =>
                        [
                            0 => 'application/vnd.nokia.n-gage.data',
                        ],
                    'n-gage' =>
                        [
                            0 => 'application/vnd.nokia.n-gage.symbian.install',
                        ],
                    'rpst' =>
                        [
                            0 => 'application/vnd.nokia.radio-preset',
                        ],
                    'rpss' =>
                        [
                            0 => 'application/vnd.nokia.radio-presets',
                        ],
                    'edm' =>
                        [
                            0 => 'application/vnd.novadigm.edm',
                        ],
                    'edx' =>
                        [
                            0 => 'application/vnd.novadigm.edx',
                        ],
                    'ext' =>
                        [
                            0 => 'application/vnd.novadigm.ext',
                        ],
                    'odc' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.chart',
                        ],
                    'otc' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.chart-template',
                        ],
                    'odb' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.database',
                        ],
                    'odf' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.formula',
                        ],
                    'odft' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.formula-template',
                        ],
                    'odg' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.graphics',
                        ],
                    'otg' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.graphics-template',
                        ],
                    'odi' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.image',
                        ],
                    'oti' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.image-template',
                        ],
                    'odp' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.presentation',
                        ],
                    'otp' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.presentation-template',
                        ],
                    'ods' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.spreadsheet',
                        ],
                    'ots' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.spreadsheet-template',
                        ],
                    'odt' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.text',
                        ],
                    'odm' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.text-master',
                        ],
                    'ott' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.text-template',
                        ],
                    'oth' =>
                        [
                            0 => 'application/vnd.oasis.opendocument.text-web',
                        ],
                    'xo' =>
                        [
                            0 => 'application/vnd.olpc-sugar',
                        ],
                    'dd2' =>
                        [
                            0 => 'application/vnd.oma.dd2+xml',
                        ],
                    'oxt' =>
                        [
                            0 => 'application/vnd.openofficeorg.extension',
                        ],
                    'pptx' =>
                        [
                            0 => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        ],
                    'sldx' =>
                        [
                            0 => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
                        ],
                    'ppsx' =>
                        [
                            0 => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                        ],
                    'potx' =>
                        [
                            0 => 'application/vnd.openxmlformats-officedocument.presentationml.template',
                        ],
                    'xlsx' =>
                        [
                            0 => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ],
                    'xltx' =>
                        [
                            0 => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
                        ],
                    'docx' =>
                        [
                            0 => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                    'dotx' =>
                        [
                            0 => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
                        ],
                    'mgp' =>
                        [
                            0 => 'application/vnd.osgeo.mapguide.package',
                        ],
                    'dp' =>
                        [
                            0 => 'application/vnd.osgi.dp',
                        ],
                    'esa' =>
                        [
                            0 => 'application/vnd.osgi.subsystem',
                        ],
                    'pdb' =>
                        [
                            0 => 'application/vnd.palm',
                        ],
                    'pqa' =>
                        [
                            0 => 'application/vnd.palm',
                        ],
                    'oprc' =>
                        [
                            0 => 'application/vnd.palm',
                        ],
                    'paw' =>
                        [
                            0 => 'application/vnd.pawaafile',
                        ],
                    'str' =>
                        [
                            0 => 'application/vnd.pg.format',
                        ],
                    'ei6' =>
                        [
                            0 => 'application/vnd.pg.osasli',
                        ],
                    'efif' =>
                        [
                            0 => 'application/vnd.picsel',
                        ],
                    'wg' =>
                        [
                            0 => 'application/vnd.pmi.widget',
                        ],
                    'plf' =>
                        [
                            0 => 'application/vnd.pocketlearn',
                        ],
                    'pbd' =>
                        [
                            0 => 'application/vnd.powerbuilder6',
                        ],
                    'box' =>
                        [
                            0 => 'application/vnd.previewsystems.box',
                        ],
                    'mgz' =>
                        [
                            0 => 'application/vnd.proteus.magazine',
                        ],
                    'qps' =>
                        [
                            0 => 'application/vnd.publishare-delta-tree',
                        ],
                    'ptid' =>
                        [
                            0 => 'application/vnd.pvi.ptid1',
                        ],
                    'qxd' =>
                        [
                            0 => 'application/vnd.quark.quarkxpress',
                        ],
                    'qxt' =>
                        [
                            0 => 'application/vnd.quark.quarkxpress',
                        ],
                    'qwd' =>
                        [
                            0 => 'application/vnd.quark.quarkxpress',
                        ],
                    'qwt' =>
                        [
                            0 => 'application/vnd.quark.quarkxpress',
                        ],
                    'qxl' =>
                        [
                            0 => 'application/vnd.quark.quarkxpress',
                        ],
                    'qxb' =>
                        [
                            0 => 'application/vnd.quark.quarkxpress',
                        ],
                    'bed' =>
                        [
                            0 => 'application/vnd.realvnc.bed',
                        ],
                    'mxl' =>
                        [
                            0 => 'application/vnd.recordare.musicxml',
                        ],
                    'musicxml' =>
                        [
                            0 => 'application/vnd.recordare.musicxml+xml',
                        ],
                    'cryptonote' =>
                        [
                            0 => 'application/vnd.rig.cryptonote',
                        ],
                    'cod' =>
                        [
                            0 => 'application/vnd.rim.cod',
                        ],
                    'rm' =>
                        [
                            0 => 'application/vnd.rn-realmedia',
                        ],
                    'rmvb' =>
                        [
                            0 => 'application/vnd.rn-realmedia-vbr',
                        ],
                    'link66' =>
                        [
                            0 => 'application/vnd.route66.link66+xml',
                        ],
                    'st' =>
                        [
                            0 => 'application/vnd.sailingtracker.track',
                        ],
                    'see' =>
                        [
                            0 => 'application/vnd.seemail',
                        ],
                    'sema' =>
                        [
                            0 => 'application/vnd.sema',
                        ],
                    'semd' =>
                        [
                            0 => 'application/vnd.semd',
                        ],
                    'semf' =>
                        [
                            0 => 'application/vnd.semf',
                        ],
                    'ifm' =>
                        [
                            0 => 'application/vnd.shana.informed.formdata',
                        ],
                    'itp' =>
                        [
                            0 => 'application/vnd.shana.informed.formtemplate',
                        ],
                    'iif' =>
                        [
                            0 => 'application/vnd.shana.informed.interchange',
                        ],
                    'ipk' =>
                        [
                            0 => 'application/vnd.shana.informed.package',
                        ],
                    'twd' =>
                        [
                            0 => 'application/vnd.simtech-mindmapper',
                        ],
                    'twds' =>
                        [
                            0 => 'application/vnd.simtech-mindmapper',
                        ],
                    'mmf' =>
                        [
                            0 => 'application/vnd.smaf',
                        ],
                    'teacher' =>
                        [
                            0 => 'application/vnd.smart.teacher',
                        ],
                    'sdkm' =>
                        [
                            0 => 'application/vnd.solent.sdkm+xml',
                        ],
                    'sdkd' =>
                        [
                            0 => 'application/vnd.solent.sdkm+xml',
                        ],
                    'dxp' =>
                        [
                            0 => 'application/vnd.spotfire.dxp',
                        ],
                    'sfs' =>
                        [
                            0 => 'application/vnd.spotfire.sfs',
                        ],
                    'sdc' =>
                        [
                            0 => 'application/vnd.stardivision.calc',
                        ],
                    'sda' =>
                        [
                            0 => 'application/vnd.stardivision.draw',
                        ],
                    'sdd' =>
                        [
                            0 => 'application/vnd.stardivision.impress',
                        ],
                    'smf' =>
                        [
                            0 => 'application/vnd.stardivision.math',
                        ],
                    'sdw' =>
                        [
                            0 => 'application/vnd.stardivision.writer',
                        ],
                    'vor' =>
                        [
                            0 => 'application/vnd.stardivision.writer',
                        ],
                    'sgl' =>
                        [
                            0 => 'application/vnd.stardivision.writer-global',
                        ],
                    'smzip' =>
                        [
                            0 => 'application/vnd.stepmania.package',
                        ],
                    'sm' =>
                        [
                            0 => 'application/vnd.stepmania.stepchart',
                        ],
                    'sxc' =>
                        [
                            0 => 'application/vnd.sun.xml.calc',
                        ],
                    'stc' =>
                        [
                            0 => 'application/vnd.sun.xml.calc.template',
                        ],
                    'sxd' =>
                        [
                            0 => 'application/vnd.sun.xml.draw',
                        ],
                    'std' =>
                        [
                            0 => 'application/vnd.sun.xml.draw.template',
                        ],
                    'sxi' =>
                        [
                            0 => 'application/vnd.sun.xml.impress',
                        ],
                    'sti' =>
                        [
                            0 => 'application/vnd.sun.xml.impress.template',
                        ],
                    'sxm' =>
                        [
                            0 => 'application/vnd.sun.xml.math',
                        ],
                    'sxw' =>
                        [
                            0 => 'application/vnd.sun.xml.writer',
                        ],
                    'sxg' =>
                        [
                            0 => 'application/vnd.sun.xml.writer.global',
                        ],
                    'stw' =>
                        [
                            0 => 'application/vnd.sun.xml.writer.template',
                        ],
                    'sus' =>
                        [
                            0 => 'application/vnd.sus-calendar',
                        ],
                    'susp' =>
                        [
                            0 => 'application/vnd.sus-calendar',
                        ],
                    'svd' =>
                        [
                            0 => 'application/vnd.svd',
                        ],
                    'sis' =>
                        [
                            0 => 'application/vnd.symbian.install',
                        ],
                    'sisx' =>
                        [
                            0 => 'application/vnd.symbian.install',
                        ],
                    'xsm' =>
                        [
                            0 => 'application/vnd.syncml+xml',
                        ],
                    'bdm' =>
                        [
                            0 => 'application/vnd.syncml.dm+wbxml',
                        ],
                    'xdm' =>
                        [
                            0 => 'application/vnd.syncml.dm+xml',
                        ],
                    'tao' =>
                        [
                            0 => 'application/vnd.tao.intent-module-archive',
                        ],
                    'pcap' =>
                        [
                            0 => 'application/vnd.tcpdump.pcap',
                        ],
                    'cap' =>
                        [
                            0 => 'application/vnd.tcpdump.pcap',
                        ],
                    'dmp' =>
                        [
                            0 => 'application/vnd.tcpdump.pcap',
                        ],
                    'tmo' =>
                        [
                            0 => 'application/vnd.tmobile-livetv',
                        ],
                    'tpt' =>
                        [
                            0 => 'application/vnd.trid.tpt',
                        ],
                    'mxs' =>
                        [
                            0 => 'application/vnd.triscape.mxs',
                        ],
                    'tra' =>
                        [
                            0 => 'application/vnd.trueapp',
                        ],
                    'ufd' =>
                        [
                            0 => 'application/vnd.ufdl',
                        ],
                    'ufdl' =>
                        [
                            0 => 'application/vnd.ufdl',
                        ],
                    'utz' =>
                        [
                            0 => 'application/vnd.uiq.theme',
                        ],
                    'umj' =>
                        [
                            0 => 'application/vnd.umajin',
                        ],
                    'unityweb' =>
                        [
                            0 => 'application/vnd.unity',
                        ],
                    'uoml' =>
                        [
                            0 => 'application/vnd.uoml+xml',
                        ],
                    'vcx' =>
                        [
                            0 => 'application/vnd.vcx',
                        ],
                    'vsd' =>
                        [
                            0 => 'application/vnd.visio',
                        ],
                    'vst' =>
                        [
                            0 => 'application/vnd.visio',
                        ],
                    'vss' =>
                        [
                            0 => 'application/vnd.visio',
                        ],
                    'vsw' =>
                        [
                            0 => 'application/vnd.visio',
                        ],
                    'vis' =>
                        [
                            0 => 'application/vnd.visionary',
                        ],
                    'vsf' =>
                        [
                            0 => 'application/vnd.vsf',
                        ],
                    'wbxml' =>
                        [
                            0 => 'application/vnd.wap.wbxml',
                        ],
                    'wmlc' =>
                        [
                            0 => 'application/vnd.wap.wmlc',
                        ],
                    'wmlsc' =>
                        [
                            0 => 'application/vnd.wap.wmlscriptc',
                        ],
                    'wtb' =>
                        [
                            0 => 'application/vnd.webturbo',
                        ],
                    'nbp' =>
                        [
                            0 => 'application/vnd.wolfram.player',
                        ],
                    'wpd' =>
                        [
                            0 => 'application/vnd.wordperfect',
                        ],
                    'wqd' =>
                        [
                            0 => 'application/vnd.wqd',
                        ],
                    'stf' =>
                        [
                            0 => 'application/vnd.wt.stf',
                        ],
                    'xar' =>
                        [
                            0 => 'application/vnd.xara',
                        ],
                    'xfdl' =>
                        [
                            0 => 'application/vnd.xfdl',
                        ],
                    'hvd' =>
                        [
                            0 => 'application/vnd.yamaha.hv-dic',
                        ],
                    'hvs' =>
                        [
                            0 => 'application/vnd.yamaha.hv-script',
                        ],
                    'hvp' =>
                        [
                            0 => 'application/vnd.yamaha.hv-voice',
                        ],
                    'osf' =>
                        [
                            0 => 'application/vnd.yamaha.openscoreformat',
                        ],
                    'osfpvg' =>
                        [
                            0 => 'application/vnd.yamaha.openscoreformat.osfpvg+xml',
                        ],
                    'saf' =>
                        [
                            0 => 'application/vnd.yamaha.smaf-audio',
                        ],
                    'spf' =>
                        [
                            0 => 'application/vnd.yamaha.smaf-phrase',
                        ],
                    'cmp' =>
                        [
                            0 => 'application/vnd.yellowriver-custom-menu',
                        ],
                    'zir' =>
                        [
                            0 => 'application/vnd.zul',
                        ],
                    'zirz' =>
                        [
                            0 => 'application/vnd.zul',
                        ],
                    'zaz' =>
                        [
                            0 => 'application/vnd.zzazz.deck+xml',
                        ],
                    'vxml' =>
                        [
                            0 => 'application/voicexml+xml',
                        ],
                    'wgt' =>
                        [
                            0 => 'application/widget',
                        ],
                    'hlp' =>
                        [
                            0 => 'application/winhlp',
                        ],
                    'wsdl' =>
                        [
                            0 => 'application/wsdl+xml',
                        ],
                    'wspolicy' =>
                        [
                            0 => 'application/wspolicy+xml',
                        ],
                    '7z' =>
                        [
                            0 => 'application/x-7z-compressed',
                        ],
                    'abw' =>
                        [
                            0 => 'application/x-abiword',
                        ],
                    'ace' =>
                        [
                            0 => 'application/x-ace-compressed',
                        ],
                    'dmg' =>
                        [
                            0 => 'application/x-apple-diskimage',
                        ],
                    'aab' =>
                        [
                            0 => 'application/x-authorware-bin',
                        ],
                    'x32' =>
                        [
                            0 => 'application/x-authorware-bin',
                        ],
                    'u32' =>
                        [
                            0 => 'application/x-authorware-bin',
                        ],
                    'vox' =>
                        [
                            0 => 'application/x-authorware-bin',
                        ],
                    'aam' =>
                        [
                            0 => 'application/x-authorware-map',
                        ],
                    'aas' =>
                        [
                            0 => 'application/x-authorware-seg',
                        ],
                    'bcpio' =>
                        [
                            0 => 'application/x-bcpio',
                        ],
                    'torrent' =>
                        [
                            0 => 'application/x-bittorrent',
                        ],
                    'blb' =>
                        [
                            0 => 'application/x-blorb',
                        ],
                    'blorb' =>
                        [
                            0 => 'application/x-blorb',
                        ],
                    'bz' =>
                        [
                            0 => 'application/x-bzip',
                        ],
                    'bz2' =>
                        [
                            0 => 'application/x-bzip2',
                        ],
                    'boz' =>
                        [
                            0 => 'application/x-bzip2',
                        ],
                    'cbr' =>
                        [
                            0 => 'application/x-cbr',
                        ],
                    'cba' =>
                        [
                            0 => 'application/x-cbr',
                        ],
                    'cbt' =>
                        [
                            0 => 'application/x-cbr',
                        ],
                    'cbz' =>
                        [
                            0 => 'application/x-cbr',
                        ],
                    'cb7' =>
                        [
                            0 => 'application/x-cbr',
                        ],
                    'vcd' =>
                        [
                            0 => 'application/x-cdlink',
                        ],
                    'cfs' =>
                        [
                            0 => 'application/x-cfs-compressed',
                        ],
                    'chat' =>
                        [
                            0 => 'application/x-chat',
                        ],
                    'pgn' =>
                        [
                            0 => 'application/x-chess-pgn',
                        ],
                    'nsc' =>
                        [
                            0 => 'application/x-conference',
                        ],
                    'cpio' =>
                        [
                            0 => 'application/x-cpio',
                        ],
                    'csh' =>
                        [
                            0 => 'application/x-csh',
                        ],
                    'deb' =>
                        [
                            0 => 'application/x-debian-package',
                        ],
                    'udeb' =>
                        [
                            0 => 'application/x-debian-package',
                        ],
                    'dgc' =>
                        [
                            0 => 'application/x-dgc-compressed',
                        ],
                    'dir' =>
                        [
                            0 => 'application/x-director',
                        ],
                    'dcr' =>
                        [
                            0 => 'application/x-director',
                        ],
                    'dxr' =>
                        [
                            0 => 'application/x-director',
                        ],
                    'cst' =>
                        [
                            0 => 'application/x-director',
                        ],
                    'cct' =>
                        [
                            0 => 'application/x-director',
                        ],
                    'cxt' =>
                        [
                            0 => 'application/x-director',
                        ],
                    'w3d' =>
                        [
                            0 => 'application/x-director',
                        ],
                    'fgd' =>
                        [
                            0 => 'application/x-director',
                        ],
                    'swa' =>
                        [
                            0 => 'application/x-director',
                        ],
                    'wad' =>
                        [
                            0 => 'application/x-doom',
                        ],
                    'ncx' =>
                        [
                            0 => 'application/x-dtbncx+xml',
                        ],
                    'dtb' =>
                        [
                            0 => 'application/x-dtbook+xml',
                        ],
                    'res' =>
                        [
                            0 => 'application/x-dtbresource+xml',
                        ],
                    'dvi' =>
                        [
                            0 => 'application/x-dvi',
                        ],
                    'evy' =>
                        [
                            0 => 'application/x-envoy',
                        ],
                    'eva' =>
                        [
                            0 => 'application/x-eva',
                        ],
                    'bdf' =>
                        [
                            0 => 'application/x-font-bdf',
                        ],
                    'gsf' =>
                        [
                            0 => 'application/x-font-ghostscript',
                        ],
                    'psf' =>
                        [
                            0 => 'application/x-font-linux-psf',
                        ],
                    'pcf' =>
                        [
                            0 => 'application/x-font-pcf',
                        ],
                    'snf' =>
                        [
                            0 => 'application/x-font-snf',
                        ],
                    'pfa' =>
                        [
                            0 => 'application/x-font-type1',
                        ],
                    'pfb' =>
                        [
                            0 => 'application/x-font-type1',
                        ],
                    'pfm' =>
                        [
                            0 => 'application/x-font-type1',
                        ],
                    'afm' =>
                        [
                            0 => 'application/x-font-type1',
                        ],
                    'arc' =>
                        [
                            0 => 'application/x-freearc',
                        ],
                    'spl' =>
                        [
                            0 => 'application/x-futuresplash',
                        ],
                    'gca' =>
                        [
                            0 => 'application/x-gca-compressed',
                        ],
                    'ulx' =>
                        [
                            0 => 'application/x-glulx',
                        ],
                    'gnumeric' =>
                        [
                            0 => 'application/x-gnumeric',
                        ],
                    'gramps' =>
                        [
                            0 => 'application/x-gramps-xml',
                        ],
                    'gtar' =>
                        [
                            0 => 'application/x-gtar',
                        ],
                    'hdf' =>
                        [
                            0 => 'application/x-hdf',
                        ],
                    'install' =>
                        [
                            0 => 'application/x-install-instructions',
                        ],
                    'iso' =>
                        [
                            0 => 'application/x-iso9660-image',
                        ],
                    'jnlp' =>
                        [
                            0 => 'application/x-java-jnlp-file',
                        ],
                    'latex' =>
                        [
                            0 => 'application/x-latex',
                        ],
                    'lzh' =>
                        [
                            0 => 'application/x-lzh-compressed',
                        ],
                    'lha' =>
                        [
                            0 => 'application/x-lzh-compressed',
                        ],
                    'mie' =>
                        [
                            0 => 'application/x-mie',
                        ],
                    'prc' =>
                        [
                            0 => 'application/x-mobipocket-ebook',
                        ],
                    'mobi' =>
                        [
                            0 => 'application/x-mobipocket-ebook',
                        ],
                    'application' =>
                        [
                            0 => 'application/x-ms-application',
                        ],
                    'lnk' =>
                        [
                            0 => 'application/x-ms-shortcut',
                        ],
                    'wmd' =>
                        [
                            0 => 'application/x-ms-wmd',
                        ],
                    'wmz' =>
                        [
                            0 => 'application/x-ms-wmz',
                            1 => 'application/x-msmetafile',
                        ],
                    'xbap' =>
                        [
                            0 => 'application/x-ms-xbap',
                        ],
                    'mdb' =>
                        [
                            0 => 'application/x-msaccess',
                        ],
                    'obd' =>
                        [
                            0 => 'application/x-msbinder',
                        ],
                    'crd' =>
                        [
                            0 => 'application/x-mscardfile',
                        ],
                    'clp' =>
                        [
                            0 => 'application/x-msclip',
                        ],
                    'exe' =>
                        [
                            0 => 'application/x-msdownload',
                        ],
                    'dll' =>
                        [
                            0 => 'application/x-msdownload',
                        ],
                    'com' =>
                        [
                            0 => 'application/x-msdownload',
                        ],
                    'bat' =>
                        [
                            0 => 'application/x-msdownload',
                        ],
                    'msi' =>
                        [
                            0 => 'application/x-msdownload',
                        ],
                    'mvb' =>
                        [
                            0 => 'application/x-msmediaview',
                        ],
                    'm13' =>
                        [
                            0 => 'application/x-msmediaview',
                        ],
                    'm14' =>
                        [
                            0 => 'application/x-msmediaview',
                        ],
                    'wmf' =>
                        [
                            0 => 'application/x-msmetafile',
                        ],
                    'emf' =>
                        [
                            0 => 'application/x-msmetafile',
                        ],
                    'emz' =>
                        [
                            0 => 'application/x-msmetafile',
                        ],
                    'mny' =>
                        [
                            0 => 'application/x-msmoney',
                        ],
                    'pub' =>
                        [
                            0 => 'application/x-mspublisher',
                        ],
                    'scd' =>
                        [
                            0 => 'application/x-msschedule',
                        ],
                    'trm' =>
                        [
                            0 => 'application/x-msterminal',
                        ],
                    'wri' =>
                        [
                            0 => 'application/x-mswrite',
                        ],
                    'nc' =>
                        [
                            0 => 'application/x-netcdf',
                        ],
                    'cdf' =>
                        [
                            0 => 'application/x-netcdf',
                        ],
                    'nzb' =>
                        [
                            0 => 'application/x-nzb',
                        ],
                    'p12' =>
                        [
                            0 => 'application/x-pkcs12',
                        ],
                    'pfx' =>
                        [
                            0 => 'application/x-pkcs12',
                        ],
                    'p7b' =>
                        [
                            0 => 'application/x-pkcs7-certificates',
                        ],
                    'spc' =>
                        [
                            0 => 'application/x-pkcs7-certificates',
                        ],
                    'p7r' =>
                        [
                            0 => 'application/x-pkcs7-certreqresp',
                        ],
                    'rar' =>
                        [
                            0 => 'application/x-rar-compressed',
                        ],
                    'ris' =>
                        [
                            0 => 'application/x-research-info-systems',
                        ],
                    'sh' =>
                        [
                            0 => 'application/x-sh',
                        ],
                    'shar' =>
                        [
                            0 => 'application/x-shar',
                        ],
                    'swf' =>
                        [
                            0 => 'application/x-shockwave-flash',
                        ],
                    'xap' =>
                        [
                            0 => 'application/x-silverlight-app',
                        ],
                    'sql' =>
                        [
                            0 => 'application/x-sql',
                        ],
                    'sit' =>
                        [
                            0 => 'application/x-stuffit',
                        ],
                    'sitx' =>
                        [
                            0 => 'application/x-stuffitx',
                        ],
                    'srt' =>
                        [
                            0 => 'application/x-subrip',
                        ],
                    'sv4cpio' =>
                        [
                            0 => 'application/x-sv4cpio',
                        ],
                    'sv4crc' =>
                        [
                            0 => 'application/x-sv4crc',
                        ],
                    't3' =>
                        [
                            0 => 'application/x-t3vm-image',
                        ],
                    'gam' =>
                        [
                            0 => 'application/x-tads',
                        ],
                    'tar' =>
                        [
                            0 => 'application/x-tar',
                        ],
                    'tcl' =>
                        [
                            0 => 'application/x-tcl',
                        ],
                    'tex' =>
                        [
                            0 => 'application/x-tex',
                        ],
                    'tfm' =>
                        [
                            0 => 'application/x-tex-tfm',
                        ],
                    'texinfo' =>
                        [
                            0 => 'application/x-texinfo',
                        ],
                    'texi' =>
                        [
                            0 => 'application/x-texinfo',
                        ],
                    'obj' =>
                        [
                            0 => 'application/x-tgif',
                        ],
                    'ustar' =>
                        [
                            0 => 'application/x-ustar',
                        ],
                    'src' =>
                        [
                            0 => 'application/x-wais-source',
                        ],
                    'der' =>
                        [
                            0 => 'application/x-x509-ca-cert',
                        ],
                    'crt' =>
                        [
                            0 => 'application/x-x509-ca-cert',
                        ],
                    'fig' =>
                        [
                            0 => 'application/x-xfig',
                        ],
                    'xlf' =>
                        [
                            0 => 'application/x-xliff+xml',
                        ],
                    'xpi' =>
                        [
                            0 => 'application/x-xpinstall',
                        ],
                    'xz' =>
                        [
                            0 => 'application/x-xz',
                        ],
                    'z1' =>
                        [
                            0 => 'application/x-zmachine',
                        ],
                    'z2' =>
                        [
                            0 => 'application/x-zmachine',
                        ],
                    'z3' =>
                        [
                            0 => 'application/x-zmachine',
                        ],
                    'z4' =>
                        [
                            0 => 'application/x-zmachine',
                        ],
                    'z5' =>
                        [
                            0 => 'application/x-zmachine',
                        ],
                    'z6' =>
                        [
                            0 => 'application/x-zmachine',
                        ],
                    'z7' =>
                        [
                            0 => 'application/x-zmachine',
                        ],
                    'z8' =>
                        [
                            0 => 'application/x-zmachine',
                        ],
                    'xaml' =>
                        [
                            0 => 'application/xaml+xml',
                        ],
                    'xdf' =>
                        [
                            0 => 'application/xcap-diff+xml',
                        ],
                    'xenc' =>
                        [
                            0 => 'application/xenc+xml',
                        ],
                    'xhtml' =>
                        [
                            0 => 'application/xhtml+xml',
                        ],
                    'xht' =>
                        [
                            0 => 'application/xhtml+xml',
                        ],
                    'xml' =>
                        [
                            0 => 'application/xml',
                        ],
                    'xsl' =>
                        [
                            0 => 'application/xml',
                        ],
                    'dtd' =>
                        [
                            0 => 'application/xml-dtd',
                        ],
                    'xop' =>
                        [
                            0 => 'application/xop+xml',
                        ],
                    'xpl' =>
                        [
                            0 => 'application/xproc+xml',
                        ],
                    'xslt' =>
                        [
                            0 => 'application/xslt+xml',
                        ],
                    'xspf' =>
                        [
                            0 => 'application/xspf+xml',
                        ],
                    'mxml' =>
                        [
                            0 => 'application/xv+xml',
                        ],
                    'xhvml' =>
                        [
                            0 => 'application/xv+xml',
                        ],
                    'xvml' =>
                        [
                            0 => 'application/xv+xml',
                        ],
                    'xvm' =>
                        [
                            0 => 'application/xv+xml',
                        ],
                    'yang' =>
                        [
                            0 => 'application/yang',
                        ],
                    'yin' =>
                        [
                            0 => 'application/yin+xml',
                        ],
                    'adp' =>
                        [
                            0 => 'audio/adpcm',
                        ],
                    'au' =>
                        [
                            0 => 'audio/basic',
                        ],
                    'snd' =>
                        [
                            0 => 'audio/basic',
                        ],
                    'mid' =>
                        [
                            0 => 'audio/midi',
                        ],
                    'midi' =>
                        [
                            0 => 'audio/midi',
                        ],
                    'kar' =>
                        [
                            0 => 'audio/midi',
                        ],
                    'rmi' =>
                        [
                            0 => 'audio/midi',
                        ],
                    'm4a' =>
                        [
                            0 => 'audio/mp4',
                        ],
                    'mp4a' =>
                        [
                            0 => 'audio/mp4',
                        ],
                    'oga' =>
                        [
                            0 => 'audio/ogg',
                        ],
                    'ogg' =>
                        [
                            0 => 'audio/ogg',
                        ],
                    'spx' =>
                        [
                            0 => 'audio/ogg',
                        ],
                    's3m' =>
                        [
                            0 => 'audio/s3m',
                        ],
                    'sil' =>
                        [
                            0 => 'audio/silk',
                        ],
                    'uva' =>
                        [
                            0 => 'audio/vnd.dece.audio',
                        ],
                    'uvva' =>
                        [
                            0 => 'audio/vnd.dece.audio',
                        ],
                    'eol' =>
                        [
                            0 => 'audio/vnd.digital-winds',
                        ],
                    'dra' =>
                        [
                            0 => 'audio/vnd.dra',
                        ],
                    'dts' =>
                        [
                            0 => 'audio/vnd.dts',
                        ],
                    'dtshd' =>
                        [
                            0 => 'audio/vnd.dts.hd',
                        ],
                    'lvp' =>
                        [
                            0 => 'audio/vnd.lucent.voice',
                        ],
                    'pya' =>
                        [
                            0 => 'audio/vnd.ms-playready.media.pya',
                        ],
                    'ecelp4800' =>
                        [
                            0 => 'audio/vnd.nuera.ecelp4800',
                        ],
                    'ecelp7470' =>
                        [
                            0 => 'audio/vnd.nuera.ecelp7470',
                        ],
                    'ecelp9600' =>
                        [
                            0 => 'audio/vnd.nuera.ecelp9600',
                        ],
                    'rip' =>
                        [
                            0 => 'audio/vnd.rip',
                        ],
                    'weba' =>
                        [
                            0 => 'audio/webm',
                        ],
                    'aac' =>
                        [
                            0 => 'audio/x-aac',
                        ],
                    'aif' =>
                        [
                            0 => 'audio/x-aiff',
                        ],
                    'aiff' =>
                        [
                            0 => 'audio/x-aiff',
                        ],
                    'aifc' =>
                        [
                            0 => 'audio/x-aiff',
                        ],
                    'caf' =>
                        [
                            0 => 'audio/x-caf',
                        ],
                    'flac' =>
                        [
                            0 => 'audio/x-flac',
                        ],
                    'mka' =>
                        [
                            0 => 'audio/x-matroska',
                        ],
                    'm3u' =>
                        [
                            0 => 'audio/x-mpegurl',
                        ],
                    'wax' =>
                        [
                            0 => 'audio/x-ms-wax',
                        ],
                    'wma' =>
                        [
                            0 => 'audio/x-ms-wma',
                        ],
                    'ram' =>
                        [
                            0 => 'audio/x-pn-realaudio',
                        ],
                    'ra' =>
                        [
                            0 => 'audio/x-pn-realaudio',
                        ],
                    'rmp' =>
                        [
                            0 => 'audio/x-pn-realaudio-plugin',
                        ],
                    'wav' =>
                        [
                            0 => 'audio/x-wav',
                        ],
                    'xm' =>
                        [
                            0 => 'audio/xm',
                        ],
                    'cdx' =>
                        [
                            0 => 'chemical/x-cdx',
                        ],
                    'cif' =>
                        [
                            0 => 'chemical/x-cif',
                        ],
                    'cmdf' =>
                        [
                            0 => 'chemical/x-cmdf',
                        ],
                    'cml' =>
                        [
                            0 => 'chemical/x-cml',
                        ],
                    'csml' =>
                        [
                            0 => 'chemical/x-csml',
                        ],
                    'xyz' =>
                        [
                            0 => 'chemical/x-xyz',
                        ],
                    'woff' =>
                        [
                            0 => 'font/woff',
                        ],
                    'woff2' =>
                        [
                            0 => 'font/woff2',
                        ],
                    'cgm' =>
                        [
                            0 => 'image/cgm',
                        ],
                    'g3' =>
                        [
                            0 => 'image/g3fax',
                        ],
                    'gif' =>
                        [
                            0 => 'image/gif',
                        ],
                    'ief' =>
                        [
                            0 => 'image/ief',
                        ],
                    'ktx' =>
                        [
                            0 => 'image/ktx',
                        ],
                    'png' =>
                        [
                            0 => 'image/png',
                        ],
                    'btif' =>
                        [
                            0 => 'image/prs.btif',
                        ],
                    'sgi' =>
                        [
                            0 => 'image/sgi',
                        ],
                    'svg' =>
                        [
                            0 => 'image/svg+xml',
                        ],
                    'svgz' =>
                        [
                            0 => 'image/svg+xml',
                        ],
                    'tiff' =>
                        [
                            0 => 'image/tiff',
                        ],
                    'tif' =>
                        [
                            0 => 'image/tiff',
                        ],
                    'psd' =>
                        [
                            0 => 'image/vnd.adobe.photoshop',
                        ],
                    'uvi' =>
                        [
                            0 => 'image/vnd.dece.graphic',
                        ],
                    'uvvi' =>
                        [
                            0 => 'image/vnd.dece.graphic',
                        ],
                    'uvg' =>
                        [
                            0 => 'image/vnd.dece.graphic',
                        ],
                    'uvvg' =>
                        [
                            0 => 'image/vnd.dece.graphic',
                        ],
                    'djvu' =>
                        [
                            0 => 'image/vnd.djvu',
                        ],
                    'djv' =>
                        [
                            0 => 'image/vnd.djvu',
                        ],
                    'sub' =>
                        [
                            0 => 'image/vnd.dvb.subtitle',
                            1 => 'text/vnd.dvb.subtitle',
                        ],
                    'dwg' =>
                        [
                            0 => 'image/vnd.dwg',
                        ],
                    'dxf' =>
                        [
                            0 => 'image/vnd.dxf',
                        ],
                    'fbs' =>
                        [
                            0 => 'image/vnd.fastbidsheet',
                        ],
                    'fpx' =>
                        [
                            0 => 'image/vnd.fpx',
                        ],
                    'fst' =>
                        [
                            0 => 'image/vnd.fst',
                        ],
                    'mmr' =>
                        [
                            0 => 'image/vnd.fujixerox.edmics-mmr',
                        ],
                    'rlc' =>
                        [
                            0 => 'image/vnd.fujixerox.edmics-rlc',
                        ],
                    'mdi' =>
                        [
                            0 => 'image/vnd.ms-modi',
                        ],
                    'wdp' =>
                        [
                            0 => 'image/vnd.ms-photo',
                        ],
                    'npx' =>
                        [
                            0 => 'image/vnd.net-fpx',
                        ],
                    'wbmp' =>
                        [
                            0 => 'image/vnd.wap.wbmp',
                        ],
                    'xif' =>
                        [
                            0 => 'image/vnd.xiff',
                        ],
                    'webp' =>
                        [
                            0 => 'image/webp',
                        ],
                    '3ds' =>
                        [
                            0 => 'image/x-3ds',
                        ],
                    'ras' =>
                        [
                            0 => 'image/x-cmu-raster',
                        ],
                    'cmx' =>
                        [
                            0 => 'image/x-cmx',
                        ],
                    'fh' =>
                        [
                            0 => 'image/x-freehand',
                        ],
                    'fhc' =>
                        [
                            0 => 'image/x-freehand',
                        ],
                    'fh4' =>
                        [
                            0 => 'image/x-freehand',
                        ],
                    'fh5' =>
                        [
                            0 => 'image/x-freehand',
                        ],
                    'fh7' =>
                        [
                            0 => 'image/x-freehand',
                        ],
                    'ico' =>
                        [
                            0 => 'image/x-icon',
                        ],
                    'sid' =>
                        [
                            0 => 'image/x-mrsid-image',
                        ],
                    'pcx' =>
                        [
                            0 => 'image/x-pcx',
                        ],
                    'pic' =>
                        [
                            0 => 'image/x-pict',
                        ],
                    'pct' =>
                        [
                            0 => 'image/x-pict',
                        ],
                    'pnm' =>
                        [
                            0 => 'image/x-portable-anymap',
                        ],
                    'pbm' =>
                        [
                            0 => 'image/x-portable-bitmap',
                        ],
                    'pgm' =>
                        [
                            0 => 'image/x-portable-graymap',
                        ],
                    'ppm' =>
                        [
                            0 => 'image/x-portable-pixmap',
                        ],
                    'rgb' =>
                        [
                            0 => 'image/x-rgb',
                        ],
                    'tga' =>
                        [
                            0 => 'image/x-tga',
                        ],
                    'xbm' =>
                        [
                            0 => 'image/x-xbitmap',
                        ],
                    'xpm' =>
                        [
                            0 => 'image/x-xpixmap',
                        ],
                    'xwd' =>
                        [
                            0 => 'image/x-xwindowdump',
                        ],
                    'eml' =>
                        [
                            0 => 'message/rfc822',
                        ],
                    'mime' =>
                        [
                            0 => 'message/rfc822',
                        ],
                    'igs' =>
                        [
                            0 => 'model/iges',
                        ],
                    'iges' =>
                        [
                            0 => 'model/iges',
                        ],
                    'msh' =>
                        [
                            0 => 'model/mesh',
                        ],
                    'mesh' =>
                        [
                            0 => 'model/mesh',
                        ],
                    'silo' =>
                        [
                            0 => 'model/mesh',
                        ],
                    'dae' =>
                        [
                            0 => 'model/vnd.collada+xml',
                        ],
                    'dwf' =>
                        [
                            0 => 'model/vnd.dwf',
                        ],
                    'gdl' =>
                        [
                            0 => 'model/vnd.gdl',
                        ],
                    'gtw' =>
                        [
                            0 => 'model/vnd.gtw',
                        ],
                    'mts' =>
                        [
                            0 => 'model/vnd.mts',
                        ],
                    'vtu' =>
                        [
                            0 => 'model/vnd.vtu',
                        ],
                    'wrl' =>
                        [
                            0 => 'model/vrml',
                        ],
                    'vrml' =>
                        [
                            0 => 'model/vrml',
                        ],
                    'x3db' =>
                        [
                            0 => 'model/x3d+binary',
                        ],
                    'x3dbz' =>
                        [
                            0 => 'model/x3d+binary',
                        ],
                    'x3dv' =>
                        [
                            0 => 'model/x3d+vrml',
                        ],
                    'x3dvz' =>
                        [
                            0 => 'model/x3d+vrml',
                        ],
                    'x3d' =>
                        [
                            0 => 'model/x3d+xml',
                        ],
                    'x3dz' =>
                        [
                            0 => 'model/x3d+xml',
                        ],
                    'appcache' =>
                        [
                            0 => 'text/cache-manifest',
                        ],
                    'ics' =>
                        [
                            0 => 'text/calendar',
                        ],
                    'ifb' =>
                        [
                            0 => 'text/calendar',
                        ],
                    'css' =>
                        [
                            0 => 'text/css',
                        ],
                    'csv' =>
                        [
                            0 => 'text/csv',
                        ],
                    'html' =>
                        [
                            0 => 'text/html',
                        ],
                    'htm' =>
                        [
                            0 => 'text/html',
                        ],
                    'n3' =>
                        [
                            0 => 'text/n3',
                        ],
                    'txt' =>
                        [
                            0 => 'text/plain',
                        ],
                    'text' =>
                        [
                            0 => 'text/plain',
                        ],
                    'conf' =>
                        [
                            0 => 'text/plain',
                        ],
                    'def' =>
                        [
                            0 => 'text/plain',
                        ],
                    'list' =>
                        [
                            0 => 'text/plain',
                        ],
                    'log' =>
                        [
                            0 => 'text/plain',
                        ],
                    'in' =>
                        [
                            0 => 'text/plain',
                        ],
                    'dsc' =>
                        [
                            0 => 'text/prs.lines.tag',
                        ],
                    'rtx' =>
                        [
                            0 => 'text/richtext',
                        ],
                    'sgml' =>
                        [
                            0 => 'text/sgml',
                        ],
                    'sgm' =>
                        [
                            0 => 'text/sgml',
                        ],
                    'tsv' =>
                        [
                            0 => 'text/tab-separated-values',
                        ],
                    't' =>
                        [
                            0 => 'text/troff',
                        ],
                    'tr' =>
                        [
                            0 => 'text/troff',
                        ],
                    'roff' =>
                        [
                            0 => 'text/troff',
                        ],
                    'man' =>
                        [
                            0 => 'text/troff',
                        ],
                    'me' =>
                        [
                            0 => 'text/troff',
                        ],
                    'ms' =>
                        [
                            0 => 'text/troff',
                        ],
                    'ttl' =>
                        [
                            0 => 'text/turtle',
                        ],
                    'uri' =>
                        [
                            0 => 'text/uri-list',
                        ],
                    'uris' =>
                        [
                            0 => 'text/uri-list',
                        ],
                    'urls' =>
                        [
                            0 => 'text/uri-list',
                        ],
                    'vcard' =>
                        [
                            0 => 'text/vcard',
                        ],
                    'curl' =>
                        [
                            0 => 'text/vnd.curl',
                        ],
                    'dcurl' =>
                        [
                            0 => 'text/vnd.curl.dcurl',
                        ],
                    'mcurl' =>
                        [
                            0 => 'text/vnd.curl.mcurl',
                        ],
                    'scurl' =>
                        [
                            0 => 'text/vnd.curl.scurl',
                        ],
                    'fly' =>
                        [
                            0 => 'text/vnd.fly',
                        ],
                    'flx' =>
                        [
                            0 => 'text/vnd.fmi.flexstor',
                        ],
                    'gv' =>
                        [
                            0 => 'text/vnd.graphviz',
                        ],
                    '3dml' =>
                        [
                            0 => 'text/vnd.in3d.3dml',
                        ],
                    'spot' =>
                        [
                            0 => 'text/vnd.in3d.spot',
                        ],
                    'jad' =>
                        [
                            0 => 'text/vnd.sun.j2me.app-descriptor',
                        ],
                    'wml' =>
                        [
                            0 => 'text/vnd.wap.wml',
                        ],
                    'wmls' =>
                        [
                            0 => 'text/vnd.wap.wmlscript',
                        ],
                    's' =>
                        [
                            0 => 'text/x-asm',
                        ],
                    'asm' =>
                        [
                            0 => 'text/x-asm',
                        ],
                    'c' =>
                        [
                            0 => 'text/x-c',
                        ],
                    'cc' =>
                        [
                            0 => 'text/x-c',
                        ],
                    'cxx' =>
                        [
                            0 => 'text/x-c',
                        ],
                    'cpp' =>
                        [
                            0 => 'text/x-c',
                        ],
                    'h' =>
                        [
                            0 => 'text/x-c',
                        ],
                    'hh' =>
                        [
                            0 => 'text/x-c',
                        ],
                    'dic' =>
                        [
                            0 => 'text/x-c',
                        ],
                    'f' =>
                        [
                            0 => 'text/x-fortran',
                        ],
                    'for' =>
                        [
                            0 => 'text/x-fortran',
                        ],
                    'f77' =>
                        [
                            0 => 'text/x-fortran',
                        ],
                    'f90' =>
                        [
                            0 => 'text/x-fortran',
                        ],
                    'java' =>
                        [
                            0 => 'text/x-java-source',
                        ],
                    'nfo' =>
                        [
                            0 => 'text/x-nfo',
                        ],
                    'opml' =>
                        [
                            0 => 'text/x-opml',
                        ],
                    'p' =>
                        [
                            0 => 'text/x-pascal',
                        ],
                    'pas' =>
                        [
                            0 => 'text/x-pascal',
                        ],
                    'etx' =>
                        [
                            0 => 'text/x-setext',
                        ],
                    'sfv' =>
                        [
                            0 => 'text/x-sfv',
                        ],
                    'uu' =>
                        [
                            0 => 'text/x-uuencode',
                        ],
                    'vcs' =>
                        [
                            0 => 'text/x-vcalendar',
                        ],
                    'vcf' =>
                        [
                            0 => 'text/x-vcard',
                        ],
                    '3gp' =>
                        [
                            0 => 'video/3gpp',
                        ],
                    '3g2' =>
                        [
                            0 => 'video/3gpp2',
                        ],
                    'h261' =>
                        [
                            0 => 'video/h261',
                        ],
                    'h263' =>
                        [
                            0 => 'video/h263',
                        ],
                    'h264' =>
                        [
                            0 => 'video/h264',
                        ],
                    'jpgv' =>
                        [
                            0 => 'video/jpeg',
                        ],
                    'jpm' =>
                        [
                            0 => 'video/jpm',
                        ],
                    'jpgm' =>
                        [
                            0 => 'video/jpm',
                        ],
                    'mj2' =>
                        [
                            0 => 'video/mj2',
                        ],
                    'mjp2' =>
                        [
                            0 => 'video/mj2',
                        ],
                    'mp4' =>
                        [
                            0 => 'video/mp4',
                        ],
                    'mp4v' =>
                        [
                            0 => 'video/mp4',
                        ],
                    'mpg4' =>
                        [
                            0 => 'video/mp4',
                        ],
                    'mpeg' =>
                        [
                            0 => 'video/mpeg',
                        ],
                    'mpg' =>
                        [
                            0 => 'video/mpeg',
                        ],
                    'mpe' =>
                        [
                            0 => 'video/mpeg',
                        ],
                    'm1v' =>
                        [
                            0 => 'video/mpeg',
                        ],
                    'm2v' =>
                        [
                            0 => 'video/mpeg',
                        ],
                    'ogv' =>
                        [
                            0 => 'video/ogg',
                        ],
                    'qt' =>
                        [
                            0 => 'video/quicktime',
                        ],
                    'mov' =>
                        [
                            0 => 'video/quicktime',
                        ],
                    'uvh' =>
                        [
                            0 => 'video/vnd.dece.hd',
                        ],
                    'uvvh' =>
                        [
                            0 => 'video/vnd.dece.hd',
                        ],
                    'uvm' =>
                        [
                            0 => 'video/vnd.dece.mobile',
                        ],
                    'uvvm' =>
                        [
                            0 => 'video/vnd.dece.mobile',
                        ],
                    'uvp' =>
                        [
                            0 => 'video/vnd.dece.pd',
                        ],
                    'uvvp' =>
                        [
                            0 => 'video/vnd.dece.pd',
                        ],
                    'uvs' =>
                        [
                            0 => 'video/vnd.dece.sd',
                        ],
                    'uvvs' =>
                        [
                            0 => 'video/vnd.dece.sd',
                        ],
                    'uvv' =>
                        [
                            0 => 'video/vnd.dece.video',
                        ],
                    'uvvv' =>
                        [
                            0 => 'video/vnd.dece.video',
                        ],
                    'dvb' =>
                        [
                            0 => 'video/vnd.dvb.file',
                        ],
                    'fvt' =>
                        [
                            0 => 'video/vnd.fvt',
                        ],
                    'mxu' =>
                        [
                            0 => 'video/vnd.mpegurl',
                        ],
                    'm4u' =>
                        [
                            0 => 'video/vnd.mpegurl',
                        ],
                    'pyv' =>
                        [
                            0 => 'video/vnd.ms-playready.media.pyv',
                        ],
                    'uvu' =>
                        [
                            0 => 'video/vnd.uvvu.mp4',
                        ],
                    'uvvu' =>
                        [
                            0 => 'video/vnd.uvvu.mp4',
                        ],
                    'viv' =>
                        [
                            0 => 'video/vnd.vivo',
                        ],
                    'webm' =>
                        [
                            0 => 'video/webm',
                        ],
                    'f4v' =>
                        [
                            0 => 'video/x-f4v',
                        ],
                    'fli' =>
                        [
                            0 => 'video/x-fli',
                        ],
                    'flv' =>
                        [
                            0 => 'video/x-flv',
                        ],
                    'm4v' =>
                        [
                            0 => 'video/x-m4v',
                        ],
                    'mkv' =>
                        [
                            0 => 'video/x-matroska',
                        ],
                    'mk3d' =>
                        [
                            0 => 'video/x-matroska',
                        ],
                    'mks' =>
                        [
                            0 => 'video/x-matroska',
                        ],
                    'mng' =>
                        [
                            0 => 'video/x-mng',
                        ],
                    'asf' =>
                        [
                            0 => 'video/x-ms-asf',
                        ],
                    'asx' =>
                        [
                            0 => 'video/x-ms-asf',
                        ],
                    'vob' =>
                        [
                            0 => 'video/x-ms-vob',
                        ],
                    'wm' =>
                        [
                            0 => 'video/x-ms-wm',
                        ],
                    'wmv' =>
                        [
                            0 => 'video/x-ms-wmv',
                        ],
                    'wmx' =>
                        [
                            0 => 'video/x-ms-wmx',
                        ],
                    'wvx' =>
                        [
                            0 => 'video/x-ms-wvx',
                        ],
                    'avi' =>
                        [
                            0 => 'video/x-msvideo',
                        ],
                    'movie' =>
                        [
                            0 => 'video/x-sgi-movie',
                        ],
                    'smv' =>
                        [
                            0 => 'video/x-smv',
                        ],
                    'ice' =>
                        [
                            0 => 'x-conference/x-cooltalk',
                        ],
                ],
            'extensions' =>
                [
                    'application/font-woff' =>
                        [
                            0 => 'wof',
                        ],
                    'application/php' =>
                        [
                            0 => 'php',
                        ],
                    'application/x-font-otf' =>
                        [
                            0 => 'otf',
                        ],
                    'application/x-font-ttf' =>
                        [
                            0 => 'ttf',
                            1 => 'ttc',
                        ],
                    'application/x-gzip' =>
                        [
                            0 => 'zip',
                        ],
                    'application/x-httpd-php' =>
                        [
                            0 => 'php',
                        ],
                    'application/x-httpd-php-source' =>
                        [
                            0 => 'php',
                        ],
                    'application/x-php' =>
                        [
                            0 => 'php',
                        ],
                    'audio/amr' =>
                        [
                            0 => 'amr',
                        ],
                    'audio/mpeg' =>
                        [
                            0 => 'mp3',
                            1 => 'mpga',
                            2 => 'mp2',
                            3 => 'mp2a',
                            4 => 'm2a',
                            5 => 'm3a',
                        ],
                    'image/jpeg' =>
                        [
                            0 => 'jpg',
                            1 => 'jpeg',
                            2 => 'jpe',
                        ],
                    'image/x-ms-bmp' =>
                        [
                            0 => 'bmp',
                        ],
                    'text/php' =>
                        [
                            0 => 'php',
                        ],
                    'text/x-php' =>
                        [
                            0 => 'php',
                        ],
                    'application/andrew-inset' =>
                        [
                            0 => 'ez',
                        ],
                    'application/applixware' =>
                        [
                            0 => 'aw',
                        ],
                    'application/atom+xml' =>
                        [
                            0 => 'atom',
                        ],
                    'application/atomcat+xml' =>
                        [
                            0 => 'atomcat',
                        ],
                    'application/atomsvc+xml' =>
                        [
                            0 => 'atomsvc',
                        ],
                    'application/ccxml+xml' =>
                        [
                            0 => 'ccxml',
                        ],
                    'application/cdmi-capability' =>
                        [
                            0 => 'cdmia',
                        ],
                    'application/cdmi-container' =>
                        [
                            0 => 'cdmic',
                        ],
                    'application/cdmi-domain' =>
                        [
                            0 => 'cdmid',
                        ],
                    'application/cdmi-object' =>
                        [
                            0 => 'cdmio',
                        ],
                    'application/cdmi-queue' =>
                        [
                            0 => 'cdmiq',
                        ],
                    'application/cu-seeme' =>
                        [
                            0 => 'cu',
                        ],
                    'application/davmount+xml' =>
                        [
                            0 => 'davmount',
                        ],
                    'application/docbook+xml' =>
                        [
                            0 => 'dbk',
                        ],
                    'application/dssc+der' =>
                        [
                            0 => 'dssc',
                        ],
                    'application/dssc+xml' =>
                        [
                            0 => 'xdssc',
                        ],
                    'application/ecmascript' =>
                        [
                            0 => 'ecma',
                        ],
                    'application/emma+xml' =>
                        [
                            0 => 'emma',
                        ],
                    'application/epub+zip' =>
                        [
                            0 => 'epub',
                        ],
                    'application/exi' =>
                        [
                            0 => 'exi',
                        ],
                    'application/font-tdpfr' =>
                        [
                            0 => 'pfr',
                        ],
                    'application/gml+xml' =>
                        [
                            0 => 'gml',
                        ],
                    'application/gpx+xml' =>
                        [
                            0 => 'gpx',
                        ],
                    'application/gxf' =>
                        [
                            0 => 'gxf',
                        ],
                    'application/hyperstudio' =>
                        [
                            0 => 'stk',
                        ],
                    'application/inkml+xml' =>
                        [
                            0 => 'ink',
                            1 => 'inkml',
                        ],
                    'application/ipfix' =>
                        [
                            0 => 'ipfix',
                        ],
                    'application/java-archive' =>
                        [
                            0 => 'jar',
                        ],
                    'application/java-serialized-object' =>
                        [
                            0 => 'ser',
                        ],
                    'application/java-vm' =>
                        [
                            0 => 'class',
                        ],
                    'application/javascript' =>
                        [
                            0 => 'js',
                        ],
                    'application/json' =>
                        [
                            0 => 'json',
                        ],
                    'application/jsonml+json' =>
                        [
                            0 => 'jsonml',
                        ],
                    'application/lost+xml' =>
                        [
                            0 => 'lostxml',
                        ],
                    'application/mac-binhex40' =>
                        [
                            0 => 'hqx',
                        ],
                    'application/mac-compactpro' =>
                        [
                            0 => 'cpt',
                        ],
                    'application/mads+xml' =>
                        [
                            0 => 'mads',
                        ],
                    'application/marc' =>
                        [
                            0 => 'mrc',
                        ],
                    'application/marcxml+xml' =>
                        [
                            0 => 'mrcx',
                        ],
                    'application/mathematica' =>
                        [
                            0 => 'ma',
                            1 => 'nb',
                            2 => 'mb',
                        ],
                    'application/mathml+xml' =>
                        [
                            0 => 'mathml',
                        ],
                    'application/mbox' =>
                        [
                            0 => 'mbox',
                        ],
                    'application/mediaservercontrol+xml' =>
                        [
                            0 => 'mscml',
                        ],
                    'application/metalink+xml' =>
                        [
                            0 => 'metalink',
                        ],
                    'application/metalink4+xml' =>
                        [
                            0 => 'meta4',
                        ],
                    'application/mets+xml' =>
                        [
                            0 => 'mets',
                        ],
                    'application/mods+xml' =>
                        [
                            0 => 'mods',
                        ],
                    'application/mp21' =>
                        [
                            0 => 'm21',
                            1 => 'mp21',
                        ],
                    'application/mp4' =>
                        [
                            0 => 'mp4s',
                        ],
                    'application/msword' =>
                        [
                            0 => 'doc',
                            1 => 'dot',
                        ],
                    'application/mxf' =>
                        [
                            0 => 'mxf',
                        ],
                    'application/octet-stream' =>
                        [
                            0 => 'bin',
                            1 => 'dms',
                            2 => 'lrf',
                            3 => 'mar',
                            4 => 'so',
                            5 => 'dist',
                            6 => 'distz',
                            7 => 'pkg',
                            8 => 'bpk',
                            9 => 'dump',
                            10 => 'elc',
                            11 => 'deploy',
                        ],
                    'application/oda' =>
                        [
                            0 => 'oda',
                        ],
                    'application/oebps-package+xml' =>
                        [
                            0 => 'opf',
                        ],
                    'application/ogg' =>
                        [
                            0 => 'ogx',
                        ],
                    'application/omdoc+xml' =>
                        [
                            0 => 'omdoc',
                        ],
                    'application/onenote' =>
                        [
                            0 => 'onetoc',
                            1 => 'onetoc2',
                            2 => 'onetmp',
                            3 => 'onepkg',
                        ],
                    'application/oxps' =>
                        [
                            0 => 'oxps',
                        ],
                    'application/patch-ops-error+xml' =>
                        [
                            0 => 'xer',
                        ],
                    'application/pdf' =>
                        [
                            0 => 'pdf',
                        ],
                    'application/pgp-encrypted' =>
                        [
                            0 => 'pgp',
                        ],
                    'application/pgp-signature' =>
                        [
                            0 => 'asc',
                            1 => 'sig',
                        ],
                    'application/pics-rules' =>
                        [
                            0 => 'prf',
                        ],
                    'application/pkcs10' =>
                        [
                            0 => 'p10',
                        ],
                    'application/pkcs7-mime' =>
                        [
                            0 => 'p7m',
                            1 => 'p7c',
                        ],
                    'application/pkcs7-signature' =>
                        [
                            0 => 'p7s',
                        ],
                    'application/pkcs8' =>
                        [
                            0 => 'p8',
                        ],
                    'application/pkix-attr-cert' =>
                        [
                            0 => 'ac',
                        ],
                    'application/pkix-cert' =>
                        [
                            0 => 'cer',
                        ],
                    'application/pkix-crl' =>
                        [
                            0 => 'crl',
                        ],
                    'application/pkix-pkipath' =>
                        [
                            0 => 'pkipath',
                        ],
                    'application/pkixcmp' =>
                        [
                            0 => 'pki',
                        ],
                    'application/pls+xml' =>
                        [
                            0 => 'pls',
                        ],
                    'application/postscript' =>
                        [
                            0 => 'ai',
                            1 => 'eps',
                            2 => 'ps',
                        ],
                    'application/prs.cww' =>
                        [
                            0 => 'cww',
                        ],
                    'application/pskc+xml' =>
                        [
                            0 => 'pskcxml',
                        ],
                    'application/rdf+xml' =>
                        [
                            0 => 'rdf',
                        ],
                    'application/reginfo+xml' =>
                        [
                            0 => 'rif',
                        ],
                    'application/relax-ng-compact-syntax' =>
                        [
                            0 => 'rnc',
                        ],
                    'application/resource-lists+xml' =>
                        [
                            0 => 'rl',
                        ],
                    'application/resource-lists-diff+xml' =>
                        [
                            0 => 'rld',
                        ],
                    'application/rls-services+xml' =>
                        [
                            0 => 'rs',
                        ],
                    'application/rpki-ghostbusters' =>
                        [
                            0 => 'gbr',
                        ],
                    'application/rpki-manifest' =>
                        [
                            0 => 'mft',
                        ],
                    'application/rpki-roa' =>
                        [
                            0 => 'roa',
                        ],
                    'application/rsd+xml' =>
                        [
                            0 => 'rsd',
                        ],
                    'application/rss+xml' =>
                        [
                            0 => 'rss',
                        ],
                    'application/rtf' =>
                        [
                            0 => 'rtf',
                        ],
                    'application/sbml+xml' =>
                        [
                            0 => 'sbml',
                        ],
                    'application/scvp-cv-request' =>
                        [
                            0 => 'scq',
                        ],
                    'application/scvp-cv-response' =>
                        [
                            0 => 'scs',
                        ],
                    'application/scvp-vp-request' =>
                        [
                            0 => 'spq',
                        ],
                    'application/scvp-vp-response' =>
                        [
                            0 => 'spp',
                        ],
                    'application/sdp' =>
                        [
                            0 => 'sdp',
                        ],
                    'application/set-payment-initiation' =>
                        [
                            0 => 'setpay',
                        ],
                    'application/set-registration-initiation' =>
                        [
                            0 => 'setreg',
                        ],
                    'application/shf+xml' =>
                        [
                            0 => 'shf',
                        ],
                    'application/smil+xml' =>
                        [
                            0 => 'smi',
                            1 => 'smil',
                        ],
                    'application/sparql-query' =>
                        [
                            0 => 'rq',
                        ],
                    'application/sparql-results+xml' =>
                        [
                            0 => 'srx',
                        ],
                    'application/srgs' =>
                        [
                            0 => 'gram',
                        ],
                    'application/srgs+xml' =>
                        [
                            0 => 'grxml',
                        ],
                    'application/sru+xml' =>
                        [
                            0 => 'sru',
                        ],
                    'application/ssdl+xml' =>
                        [
                            0 => 'ssdl',
                        ],
                    'application/ssml+xml' =>
                        [
                            0 => 'ssml',
                        ],
                    'application/tei+xml' =>
                        [
                            0 => 'tei',
                            1 => 'teicorpus',
                        ],
                    'application/thraud+xml' =>
                        [
                            0 => 'tfi',
                        ],
                    'application/timestamped-data' =>
                        [
                            0 => 'tsd',
                        ],
                    'application/vnd.3gpp.pic-bw-large' =>
                        [
                            0 => 'plb',
                        ],
                    'application/vnd.3gpp.pic-bw-small' =>
                        [
                            0 => 'psb',
                        ],
                    'application/vnd.3gpp.pic-bw-var' =>
                        [
                            0 => 'pvb',
                        ],
                    'application/vnd.3gpp2.tcap' =>
                        [
                            0 => 'tcap',
                        ],
                    'application/vnd.3m.post-it-notes' =>
                        [
                            0 => 'pwn',
                        ],
                    'application/vnd.accpac.simply.aso' =>
                        [
                            0 => 'aso',
                        ],
                    'application/vnd.accpac.simply.imp' =>
                        [
                            0 => 'imp',
                        ],
                    'application/vnd.acucobol' =>
                        [
                            0 => 'acu',
                        ],
                    'application/vnd.acucorp' =>
                        [
                            0 => 'atc',
                            1 => 'acutc',
                        ],
                    'application/vnd.adobe.air-application-installer-package+zip' =>
                        [
                            0 => 'air',
                        ],
                    'application/vnd.adobe.formscentral.fcdt' =>
                        [
                            0 => 'fcdt',
                        ],
                    'application/vnd.adobe.fxp' =>
                        [
                            0 => 'fxp',
                            1 => 'fxpl',
                        ],
                    'application/vnd.adobe.xdp+xml' =>
                        [
                            0 => 'xdp',
                        ],
                    'application/vnd.adobe.xfdf' =>
                        [
                            0 => 'xfdf',
                        ],
                    'application/vnd.ahead.space' =>
                        [
                            0 => 'ahead',
                        ],
                    'application/vnd.airzip.filesecure.azf' =>
                        [
                            0 => 'azf',
                        ],
                    'application/vnd.airzip.filesecure.azs' =>
                        [
                            0 => 'azs',
                        ],
                    'application/vnd.amazon.ebook' =>
                        [
                            0 => 'azw',
                        ],
                    'application/vnd.americandynamics.acc' =>
                        [
                            0 => 'acc',
                        ],
                    'application/vnd.amiga.ami' =>
                        [
                            0 => 'ami',
                        ],
                    'application/vnd.android.package-archive' =>
                        [
                            0 => 'apk',
                        ],
                    'application/vnd.anser-web-certificate-issue-initiation' =>
                        [
                            0 => 'cii',
                        ],
                    'application/vnd.anser-web-funds-transfer-initiation' =>
                        [
                            0 => 'fti',
                        ],
                    'application/vnd.antix.game-component' =>
                        [
                            0 => 'atx',
                        ],
                    'application/vnd.apple.installer+xml' =>
                        [
                            0 => 'mpkg',
                        ],
                    'application/vnd.apple.mpegurl' =>
                        [
                            0 => 'm3u8',
                        ],
                    'application/vnd.aristanetworks.swi' =>
                        [
                            0 => 'swi',
                        ],
                    'application/vnd.astraea-software.iota' =>
                        [
                            0 => 'iota',
                        ],
                    'application/vnd.audiograph' =>
                        [
                            0 => 'aep',
                        ],
                    'application/vnd.blueice.multipass' =>
                        [
                            0 => 'mpm',
                        ],
                    'application/vnd.bmi' =>
                        [
                            0 => 'bmi',
                        ],
                    'application/vnd.businessobjects' =>
                        [
                            0 => 'rep',
                        ],
                    'application/vnd.chemdraw+xml' =>
                        [
                            0 => 'cdxml',
                        ],
                    'application/vnd.chipnuts.karaoke-mmd' =>
                        [
                            0 => 'mmd',
                        ],
                    'application/vnd.cinderella' =>
                        [
                            0 => 'cdy',
                        ],
                    'application/vnd.claymore' =>
                        [
                            0 => 'cla',
                        ],
                    'application/vnd.cloanto.rp9' =>
                        [
                            0 => 'rp9',
                        ],
                    'application/vnd.clonk.c4group' =>
                        [
                            0 => 'c4g',
                            1 => 'c4d',
                            2 => 'c4f',
                            3 => 'c4p',
                            4 => 'c4u',
                        ],
                    'application/vnd.cluetrust.cartomobile-config' =>
                        [
                            0 => 'c11amc',
                        ],
                    'application/vnd.cluetrust.cartomobile-config-pkg' =>
                        [
                            0 => 'c11amz',
                        ],
                    'application/vnd.commonspace' =>
                        [
                            0 => 'csp',
                        ],
                    'application/vnd.contact.cmsg' =>
                        [
                            0 => 'cdbcmsg',
                        ],
                    'application/vnd.cosmocaller' =>
                        [
                            0 => 'cmc',
                        ],
                    'application/vnd.crick.clicker' =>
                        [
                            0 => 'clkx',
                        ],
                    'application/vnd.crick.clicker.keyboard' =>
                        [
                            0 => 'clkk',
                        ],
                    'application/vnd.crick.clicker.palette' =>
                        [
                            0 => 'clkp',
                        ],
                    'application/vnd.crick.clicker.template' =>
                        [
                            0 => 'clkt',
                        ],
                    'application/vnd.crick.clicker.wordbank' =>
                        [
                            0 => 'clkw',
                        ],
                    'application/vnd.criticaltools.wbs+xml' =>
                        [
                            0 => 'wbs',
                        ],
                    'application/vnd.ctc-posml' =>
                        [
                            0 => 'pml',
                        ],
                    'application/vnd.cups-ppd' =>
                        [
                            0 => 'ppd',
                        ],
                    'application/vnd.curl.car' =>
                        [
                            0 => 'car',
                        ],
                    'application/vnd.curl.pcurl' =>
                        [
                            0 => 'pcurl',
                        ],
                    'application/vnd.dart' =>
                        [
                            0 => 'dart',
                        ],
                    'application/vnd.data-vision.rdz' =>
                        [
                            0 => 'rdz',
                        ],
                    'application/vnd.dece.data' =>
                        [
                            0 => 'uvf',
                            1 => 'uvvf',
                            2 => 'uvd',
                            3 => 'uvvd',
                        ],
                    'application/vnd.dece.ttml+xml' =>
                        [
                            0 => 'uvt',
                            1 => 'uvvt',
                        ],
                    'application/vnd.dece.unspecified' =>
                        [
                            0 => 'uvx',
                            1 => 'uvvx',
                        ],
                    'application/vnd.dece.zip' =>
                        [
                            0 => 'uvz',
                            1 => 'uvvz',
                        ],
                    'application/vnd.denovo.fcselayout-link' =>
                        [
                            0 => 'fe_launch',
                        ],
                    'application/vnd.dna' =>
                        [
                            0 => 'dna',
                        ],
                    'application/vnd.dolby.mlp' =>
                        [
                            0 => 'mlp',
                        ],
                    'application/vnd.dpgraph' =>
                        [
                            0 => 'dpg',
                        ],
                    'application/vnd.dreamfactory' =>
                        [
                            0 => 'dfac',
                        ],
                    'application/vnd.ds-keypoint' =>
                        [
                            0 => 'kpxx',
                        ],
                    'application/vnd.dvb.ait' =>
                        [
                            0 => 'ait',
                        ],
                    'application/vnd.dvb.service' =>
                        [
                            0 => 'svc',
                        ],
                    'application/vnd.dynageo' =>
                        [
                            0 => 'geo',
                        ],
                    'application/vnd.ecowin.chart' =>
                        [
                            0 => 'mag',
                        ],
                    'application/vnd.enliven' =>
                        [
                            0 => 'nml',
                        ],
                    'application/vnd.epson.esf' =>
                        [
                            0 => 'esf',
                        ],
                    'application/vnd.epson.msf' =>
                        [
                            0 => 'msf',
                        ],
                    'application/vnd.epson.quickanime' =>
                        [
                            0 => 'qam',
                        ],
                    'application/vnd.epson.salt' =>
                        [
                            0 => 'slt',
                        ],
                    'application/vnd.epson.ssf' =>
                        [
                            0 => 'ssf',
                        ],
                    'application/vnd.eszigno3+xml' =>
                        [
                            0 => 'es3',
                            1 => 'et3',
                        ],
                    'application/vnd.ezpix-album' =>
                        [
                            0 => 'ez2',
                        ],
                    'application/vnd.ezpix-package' =>
                        [
                            0 => 'ez3',
                        ],
                    'application/vnd.fdf' =>
                        [
                            0 => 'fdf',
                        ],
                    'application/vnd.fdsn.mseed' =>
                        [
                            0 => 'mseed',
                        ],
                    'application/vnd.fdsn.seed' =>
                        [
                            0 => 'seed',
                            1 => 'dataless',
                        ],
                    'application/vnd.flographit' =>
                        [
                            0 => 'gph',
                        ],
                    'application/vnd.fluxtime.clip' =>
                        [
                            0 => 'ftc',
                        ],
                    'application/vnd.framemaker' =>
                        [
                            0 => 'fm',
                            1 => 'frame',
                            2 => 'maker',
                            3 => 'book',
                        ],
                    'application/vnd.frogans.fnc' =>
                        [
                            0 => 'fnc',
                        ],
                    'application/vnd.frogans.ltf' =>
                        [
                            0 => 'ltf',
                        ],
                    'application/vnd.fsc.weblaunch' =>
                        [
                            0 => 'fsc',
                        ],
                    'application/vnd.fujitsu.oasys' =>
                        [
                            0 => 'oas',
                        ],
                    'application/vnd.fujitsu.oasys2' =>
                        [
                            0 => 'oa2',
                        ],
                    'application/vnd.fujitsu.oasys3' =>
                        [
                            0 => 'oa3',
                        ],
                    'application/vnd.fujitsu.oasysgp' =>
                        [
                            0 => 'fg5',
                        ],
                    'application/vnd.fujitsu.oasysprs' =>
                        [
                            0 => 'bh2',
                        ],
                    'application/vnd.fujixerox.ddd' =>
                        [
                            0 => 'ddd',
                        ],
                    'application/vnd.fujixerox.docuworks' =>
                        [
                            0 => 'xdw',
                        ],
                    'application/vnd.fujixerox.docuworks.binder' =>
                        [
                            0 => 'xbd',
                        ],
                    'application/vnd.fuzzysheet' =>
                        [
                            0 => 'fzs',
                        ],
                    'application/vnd.genomatix.tuxedo' =>
                        [
                            0 => 'txd',
                        ],
                    'application/vnd.geogebra.file' =>
                        [
                            0 => 'ggb',
                        ],
                    'application/vnd.geogebra.tool' =>
                        [
                            0 => 'ggt',
                        ],
                    'application/vnd.geometry-explorer' =>
                        [
                            0 => 'gex',
                            1 => 'gre',
                        ],
                    'application/vnd.geonext' =>
                        [
                            0 => 'gxt',
                        ],
                    'application/vnd.geoplan' =>
                        [
                            0 => 'g2w',
                        ],
                    'application/vnd.geospace' =>
                        [
                            0 => 'g3w',
                        ],
                    'application/vnd.gmx' =>
                        [
                            0 => 'gmx',
                        ],
                    'application/vnd.google-earth.kml+xml' =>
                        [
                            0 => 'kml',
                        ],
                    'application/vnd.google-earth.kmz' =>
                        [
                            0 => 'kmz',
                        ],
                    'application/vnd.grafeq' =>
                        [
                            0 => 'gqf',
                            1 => 'gqs',
                        ],
                    'application/vnd.groove-account' =>
                        [
                            0 => 'gac',
                        ],
                    'application/vnd.groove-help' =>
                        [
                            0 => 'ghf',
                        ],
                    'application/vnd.groove-identity-message' =>
                        [
                            0 => 'gim',
                        ],
                    'application/vnd.groove-injector' =>
                        [
                            0 => 'grv',
                        ],
                    'application/vnd.groove-tool-message' =>
                        [
                            0 => 'gtm',
                        ],
                    'application/vnd.groove-tool-template' =>
                        [
                            0 => 'tpl',
                        ],
                    'application/vnd.groove-vcard' =>
                        [
                            0 => 'vcg',
                        ],
                    'application/vnd.hal+xml' =>
                        [
                            0 => 'hal',
                        ],
                    'application/vnd.handheld-entertainment+xml' =>
                        [
                            0 => 'zmm',
                        ],
                    'application/vnd.hbci' =>
                        [
                            0 => 'hbci',
                        ],
                    'application/vnd.hhe.lesson-player' =>
                        [
                            0 => 'les',
                        ],
                    'application/vnd.hp-hpgl' =>
                        [
                            0 => 'hpgl',
                        ],
                    'application/vnd.hp-hpid' =>
                        [
                            0 => 'hpid',
                        ],
                    'application/vnd.hp-hps' =>
                        [
                            0 => 'hps',
                        ],
                    'application/vnd.hp-jlyt' =>
                        [
                            0 => 'jlt',
                        ],
                    'application/vnd.hp-pcl' =>
                        [
                            0 => 'pcl',
                        ],
                    'application/vnd.hp-pclxl' =>
                        [
                            0 => 'pclxl',
                        ],
                    'application/vnd.hydrostatix.sof-data' =>
                        [
                            0 => 'sfd-hdstx',
                        ],
                    'application/vnd.ibm.minipay' =>
                        [
                            0 => 'mpy',
                        ],
                    'application/vnd.ibm.modcap' =>
                        [
                            0 => 'afp',
                            1 => 'listafp',
                            2 => 'list3820',
                        ],
                    'application/vnd.ibm.rights-management' =>
                        [
                            0 => 'irm',
                        ],
                    'application/vnd.ibm.secure-container' =>
                        [
                            0 => 'sc',
                        ],
                    'application/vnd.iccprofile' =>
                        [
                            0 => 'icc',
                            1 => 'icm',
                        ],
                    'application/vnd.igloader' =>
                        [
                            0 => 'igl',
                        ],
                    'application/vnd.immervision-ivp' =>
                        [
                            0 => 'ivp',
                        ],
                    'application/vnd.immervision-ivu' =>
                        [
                            0 => 'ivu',
                        ],
                    'application/vnd.insors.igm' =>
                        [
                            0 => 'igm',
                        ],
                    'application/vnd.intercon.formnet' =>
                        [
                            0 => 'xpw',
                            1 => 'xpx',
                        ],
                    'application/vnd.intergeo' =>
                        [
                            0 => 'i2g',
                        ],
                    'application/vnd.intu.qbo' =>
                        [
                            0 => 'qbo',
                        ],
                    'application/vnd.intu.qfx' =>
                        [
                            0 => 'qfx',
                        ],
                    'application/vnd.ipunplugged.rcprofile' =>
                        [
                            0 => 'rcprofile',
                        ],
                    'application/vnd.irepository.package+xml' =>
                        [
                            0 => 'irp',
                        ],
                    'application/vnd.is-xpr' =>
                        [
                            0 => 'xpr',
                        ],
                    'application/vnd.isac.fcs' =>
                        [
                            0 => 'fcs',
                        ],
                    'application/vnd.jam' =>
                        [
                            0 => 'jam',
                        ],
                    'application/vnd.jcp.javame.midlet-rms' =>
                        [
                            0 => 'rms',
                        ],
                    'application/vnd.jisp' =>
                        [
                            0 => 'jisp',
                        ],
                    'application/vnd.joost.joda-archive' =>
                        [
                            0 => 'joda',
                        ],
                    'application/vnd.kahootz' =>
                        [
                            0 => 'ktz',
                            1 => 'ktr',
                        ],
                    'application/vnd.kde.karbon' =>
                        [
                            0 => 'karbon',
                        ],
                    'application/vnd.kde.kchart' =>
                        [
                            0 => 'chrt',
                        ],
                    'application/vnd.kde.kformula' =>
                        [
                            0 => 'kfo',
                        ],
                    'application/vnd.kde.kivio' =>
                        [
                            0 => 'flw',
                        ],
                    'application/vnd.kde.kontour' =>
                        [
                            0 => 'kon',
                        ],
                    'application/vnd.kde.kpresenter' =>
                        [
                            0 => 'kpr',
                            1 => 'kpt',
                        ],
                    'application/vnd.kde.kspread' =>
                        [
                            0 => 'ksp',
                        ],
                    'application/vnd.kde.kword' =>
                        [
                            0 => 'kwd',
                            1 => 'kwt',
                        ],
                    'application/vnd.kenameaapp' =>
                        [
                            0 => 'htke',
                        ],
                    'application/vnd.kidspiration' =>
                        [
                            0 => 'kia',
                        ],
                    'application/vnd.kinar' =>
                        [
                            0 => 'kne',
                            1 => 'knp',
                        ],
                    'application/vnd.koan' =>
                        [
                            0 => 'skp',
                            1 => 'skd',
                            2 => 'skt',
                            3 => 'skm',
                        ],
                    'application/vnd.kodak-descriptor' =>
                        [
                            0 => 'sse',
                        ],
                    'application/vnd.las.las+xml' =>
                        [
                            0 => 'lasxml',
                        ],
                    'application/vnd.llamagraphics.life-balance.desktop' =>
                        [
                            0 => 'lbd',
                        ],
                    'application/vnd.llamagraphics.life-balance.exchange+xml' =>
                        [
                            0 => 'lbe',
                        ],
                    'application/vnd.lotus-1-2-3' =>
                        [
                            0 => '123',
                        ],
                    'application/vnd.lotus-approach' =>
                        [
                            0 => 'apr',
                        ],
                    'application/vnd.lotus-freelance' =>
                        [
                            0 => 'pre',
                        ],
                    'application/vnd.lotus-notes' =>
                        [
                            0 => 'nsf',
                        ],
                    'application/vnd.lotus-organizer' =>
                        [
                            0 => 'org',
                        ],
                    'application/vnd.lotus-screencam' =>
                        [
                            0 => 'scm',
                        ],
                    'application/vnd.lotus-wordpro' =>
                        [
                            0 => 'lwp',
                        ],
                    'application/vnd.macports.portpkg' =>
                        [
                            0 => 'portpkg',
                        ],
                    'application/vnd.mcd' =>
                        [
                            0 => 'mcd',
                        ],
                    'application/vnd.medcalcdata' =>
                        [
                            0 => 'mc1',
                        ],
                    'application/vnd.mediastation.cdkey' =>
                        [
                            0 => 'cdkey',
                        ],
                    'application/vnd.mfer' =>
                        [
                            0 => 'mwf',
                        ],
                    'application/vnd.mfmp' =>
                        [
                            0 => 'mfm',
                        ],
                    'application/vnd.micrografx.flo' =>
                        [
                            0 => 'flo',
                        ],
                    'application/vnd.micrografx.igx' =>
                        [
                            0 => 'igx',
                        ],
                    'application/vnd.mif' =>
                        [
                            0 => 'mif',
                        ],
                    'application/vnd.mobius.daf' =>
                        [
                            0 => 'daf',
                        ],
                    'application/vnd.mobius.dis' =>
                        [
                            0 => 'dis',
                        ],
                    'application/vnd.mobius.mbk' =>
                        [
                            0 => 'mbk',
                        ],
                    'application/vnd.mobius.mqy' =>
                        [
                            0 => 'mqy',
                        ],
                    'application/vnd.mobius.msl' =>
                        [
                            0 => 'msl',
                        ],
                    'application/vnd.mobius.plc' =>
                        [
                            0 => 'plc',
                        ],
                    'application/vnd.mobius.txf' =>
                        [
                            0 => 'txf',
                        ],
                    'application/vnd.mophun.application' =>
                        [
                            0 => 'mpn',
                        ],
                    'application/vnd.mophun.certificate' =>
                        [
                            0 => 'mpc',
                        ],
                    'application/vnd.mozilla.xul+xml' =>
                        [
                            0 => 'xul',
                        ],
                    'application/vnd.ms-artgalry' =>
                        [
                            0 => 'cil',
                        ],
                    'application/vnd.ms-cab-compressed' =>
                        [
                            0 => 'cab',
                        ],
                    'application/vnd.ms-excel' =>
                        [
                            0 => 'xls',
                            1 => 'xlm',
                            2 => 'xla',
                            3 => 'xlc',
                            4 => 'xlt',
                            5 => 'xlw',
                        ],
                    'application/vnd.ms-excel.addin.macroenabled.12' =>
                        [
                            0 => 'xlam',
                        ],
                    'application/vnd.ms-excel.sheet.binary.macroenabled.12' =>
                        [
                            0 => 'xlsb',
                        ],
                    'application/vnd.ms-excel.sheet.macroenabled.12' =>
                        [
                            0 => 'xlsm',
                        ],
                    'application/vnd.ms-excel.template.macroenabled.12' =>
                        [
                            0 => 'xltm',
                        ],
                    'application/vnd.ms-fontobject' =>
                        [
                            0 => 'eot',
                        ],
                    'application/vnd.ms-htmlhelp' =>
                        [
                            0 => 'chm',
                        ],
                    'application/vnd.ms-ims' =>
                        [
                            0 => 'ims',
                        ],
                    'application/vnd.ms-lrm' =>
                        [
                            0 => 'lrm',
                        ],
                    'application/vnd.ms-officetheme' =>
                        [
                            0 => 'thmx',
                        ],
                    'application/vnd.ms-pki.seccat' =>
                        [
                            0 => 'cat',
                        ],
                    'application/vnd.ms-pki.stl' =>
                        [
                            0 => 'stl',
                        ],
                    'application/vnd.ms-powerpoint' =>
                        [
                            0 => 'ppt',
                            1 => 'pps',
                            2 => 'pot',
                        ],
                    'application/vnd.ms-powerpoint.addin.macroenabled.12' =>
                        [
                            0 => 'ppam',
                        ],
                    'application/vnd.ms-powerpoint.presentation.macroenabled.12' =>
                        [
                            0 => 'pptm',
                        ],
                    'application/vnd.ms-powerpoint.slide.macroenabled.12' =>
                        [
                            0 => 'sldm',
                        ],
                    'application/vnd.ms-powerpoint.slideshow.macroenabled.12' =>
                        [
                            0 => 'ppsm',
                        ],
                    'application/vnd.ms-powerpoint.template.macroenabled.12' =>
                        [
                            0 => 'potm',
                        ],
                    'application/vnd.ms-project' =>
                        [
                            0 => 'mpp',
                            1 => 'mpt',
                        ],
                    'application/vnd.ms-word.document.macroenabled.12' =>
                        [
                            0 => 'docm',
                        ],
                    'application/vnd.ms-word.template.macroenabled.12' =>
                        [
                            0 => 'dotm',
                        ],
                    'application/vnd.ms-works' =>
                        [
                            0 => 'wps',
                            1 => 'wks',
                            2 => 'wcm',
                            3 => 'wdb',
                        ],
                    'application/vnd.ms-wpl' =>
                        [
                            0 => 'wpl',
                        ],
                    'application/vnd.ms-xpsdocument' =>
                        [
                            0 => 'xps',
                        ],
                    'application/vnd.mseq' =>
                        [
                            0 => 'mseq',
                        ],
                    'application/vnd.musician' =>
                        [
                            0 => 'mus',
                        ],
                    'application/vnd.muvee.style' =>
                        [
                            0 => 'msty',
                        ],
                    'application/vnd.mynfc' =>
                        [
                            0 => 'taglet',
                        ],
                    'application/vnd.neurolanguage.nlu' =>
                        [
                            0 => 'nlu',
                        ],
                    'application/vnd.nitf' =>
                        [
                            0 => 'ntf',
                            1 => 'nitf',
                        ],
                    'application/vnd.noblenet-directory' =>
                        [
                            0 => 'nnd',
                        ],
                    'application/vnd.noblenet-sealer' =>
                        [
                            0 => 'nns',
                        ],
                    'application/vnd.noblenet-web' =>
                        [
                            0 => 'nnw',
                        ],
                    'application/vnd.nokia.n-gage.data' =>
                        [
                            0 => 'ngdat',
                        ],
                    'application/vnd.nokia.n-gage.symbian.install' =>
                        [
                            0 => 'n-gage',
                        ],
                    'application/vnd.nokia.radio-preset' =>
                        [
                            0 => 'rpst',
                        ],
                    'application/vnd.nokia.radio-presets' =>
                        [
                            0 => 'rpss',
                        ],
                    'application/vnd.novadigm.edm' =>
                        [
                            0 => 'edm',
                        ],
                    'application/vnd.novadigm.edx' =>
                        [
                            0 => 'edx',
                        ],
                    'application/vnd.novadigm.ext' =>
                        [
                            0 => 'ext',
                        ],
                    'application/vnd.oasis.opendocument.chart' =>
                        [
                            0 => 'odc',
                        ],
                    'application/vnd.oasis.opendocument.chart-template' =>
                        [
                            0 => 'otc',
                        ],
                    'application/vnd.oasis.opendocument.database' =>
                        [
                            0 => 'odb',
                        ],
                    'application/vnd.oasis.opendocument.formula' =>
                        [
                            0 => 'odf',
                        ],
                    'application/vnd.oasis.opendocument.formula-template' =>
                        [
                            0 => 'odft',
                        ],
                    'application/vnd.oasis.opendocument.graphics' =>
                        [
                            0 => 'odg',
                        ],
                    'application/vnd.oasis.opendocument.graphics-template' =>
                        [
                            0 => 'otg',
                        ],
                    'application/vnd.oasis.opendocument.image' =>
                        [
                            0 => 'odi',
                        ],
                    'application/vnd.oasis.opendocument.image-template' =>
                        [
                            0 => 'oti',
                        ],
                    'application/vnd.oasis.opendocument.presentation' =>
                        [
                            0 => 'odp',
                        ],
                    'application/vnd.oasis.opendocument.presentation-template' =>
                        [
                            0 => 'otp',
                        ],
                    'application/vnd.oasis.opendocument.spreadsheet' =>
                        [
                            0 => 'ods',
                        ],
                    'application/vnd.oasis.opendocument.spreadsheet-template' =>
                        [
                            0 => 'ots',
                        ],
                    'application/vnd.oasis.opendocument.text' =>
                        [
                            0 => 'odt',
                        ],
                    'application/vnd.oasis.opendocument.text-master' =>
                        [
                            0 => 'odm',
                        ],
                    'application/vnd.oasis.opendocument.text-template' =>
                        [
                            0 => 'ott',
                        ],
                    'application/vnd.oasis.opendocument.text-web' =>
                        [
                            0 => 'oth',
                        ],
                    'application/vnd.olpc-sugar' =>
                        [
                            0 => 'xo',
                        ],
                    'application/vnd.oma.dd2+xml' =>
                        [
                            0 => 'dd2',
                        ],
                    'application/vnd.openofficeorg.extension' =>
                        [
                            0 => 'oxt',
                        ],
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation' =>
                        [
                            0 => 'pptx',
                        ],
                    'application/vnd.openxmlformats-officedocument.presentationml.slide' =>
                        [
                            0 => 'sldx',
                        ],
                    'application/vnd.openxmlformats-officedocument.presentationml.slideshow' =>
                        [
                            0 => 'ppsx',
                        ],
                    'application/vnd.openxmlformats-officedocument.presentationml.template' =>
                        [
                            0 => 'potx',
                        ],
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' =>
                        [
                            0 => 'xlsx',
                        ],
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.template' =>
                        [
                            0 => 'xltx',
                        ],
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' =>
                        [
                            0 => 'docx',
                        ],
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.template' =>
                        [
                            0 => 'dotx',
                        ],
                    'application/vnd.osgeo.mapguide.package' =>
                        [
                            0 => 'mgp',
                        ],
                    'application/vnd.osgi.dp' =>
                        [
                            0 => 'dp',
                        ],
                    'application/vnd.osgi.subsystem' =>
                        [
                            0 => 'esa',
                        ],
                    'application/vnd.palm' =>
                        [
                            0 => 'pdb',
                            1 => 'pqa',
                            2 => 'oprc',
                        ],
                    'application/vnd.pawaafile' =>
                        [
                            0 => 'paw',
                        ],
                    'application/vnd.pg.format' =>
                        [
                            0 => 'str',
                        ],
                    'application/vnd.pg.osasli' =>
                        [
                            0 => 'ei6',
                        ],
                    'application/vnd.picsel' =>
                        [
                            0 => 'efif',
                        ],
                    'application/vnd.pmi.widget' =>
                        [
                            0 => 'wg',
                        ],
                    'application/vnd.pocketlearn' =>
                        [
                            0 => 'plf',
                        ],
                    'application/vnd.powerbuilder6' =>
                        [
                            0 => 'pbd',
                        ],
                    'application/vnd.previewsystems.box' =>
                        [
                            0 => 'box',
                        ],
                    'application/vnd.proteus.magazine' =>
                        [
                            0 => 'mgz',
                        ],
                    'application/vnd.publishare-delta-tree' =>
                        [
                            0 => 'qps',
                        ],
                    'application/vnd.pvi.ptid1' =>
                        [
                            0 => 'ptid',
                        ],
                    'application/vnd.quark.quarkxpress' =>
                        [
                            0 => 'qxd',
                            1 => 'qxt',
                            2 => 'qwd',
                            3 => 'qwt',
                            4 => 'qxl',
                            5 => 'qxb',
                        ],
                    'application/vnd.realvnc.bed' =>
                        [
                            0 => 'bed',
                        ],
                    'application/vnd.recordare.musicxml' =>
                        [
                            0 => 'mxl',
                        ],
                    'application/vnd.recordare.musicxml+xml' =>
                        [
                            0 => 'musicxml',
                        ],
                    'application/vnd.rig.cryptonote' =>
                        [
                            0 => 'cryptonote',
                        ],
                    'application/vnd.rim.cod' =>
                        [
                            0 => 'cod',
                        ],
                    'application/vnd.rn-realmedia' =>
                        [
                            0 => 'rm',
                        ],
                    'application/vnd.rn-realmedia-vbr' =>
                        [
                            0 => 'rmvb',
                        ],
                    'application/vnd.route66.link66+xml' =>
                        [
                            0 => 'link66',
                        ],
                    'application/vnd.sailingtracker.track' =>
                        [
                            0 => 'st',
                        ],
                    'application/vnd.seemail' =>
                        [
                            0 => 'see',
                        ],
                    'application/vnd.sema' =>
                        [
                            0 => 'sema',
                        ],
                    'application/vnd.semd' =>
                        [
                            0 => 'semd',
                        ],
                    'application/vnd.semf' =>
                        [
                            0 => 'semf',
                        ],
                    'application/vnd.shana.informed.formdata' =>
                        [
                            0 => 'ifm',
                        ],
                    'application/vnd.shana.informed.formtemplate' =>
                        [
                            0 => 'itp',
                        ],
                    'application/vnd.shana.informed.interchange' =>
                        [
                            0 => 'iif',
                        ],
                    'application/vnd.shana.informed.package' =>
                        [
                            0 => 'ipk',
                        ],
                    'application/vnd.simtech-mindmapper' =>
                        [
                            0 => 'twd',
                            1 => 'twds',
                        ],
                    'application/vnd.smaf' =>
                        [
                            0 => 'mmf',
                        ],
                    'application/vnd.smart.teacher' =>
                        [
                            0 => 'teacher',
                        ],
                    'application/vnd.solent.sdkm+xml' =>
                        [
                            0 => 'sdkm',
                            1 => 'sdkd',
                        ],
                    'application/vnd.spotfire.dxp' =>
                        [
                            0 => 'dxp',
                        ],
                    'application/vnd.spotfire.sfs' =>
                        [
                            0 => 'sfs',
                        ],
                    'application/vnd.stardivision.calc' =>
                        [
                            0 => 'sdc',
                        ],
                    'application/vnd.stardivision.draw' =>
                        [
                            0 => 'sda',
                        ],
                    'application/vnd.stardivision.impress' =>
                        [
                            0 => 'sdd',
                        ],
                    'application/vnd.stardivision.math' =>
                        [
                            0 => 'smf',
                        ],
                    'application/vnd.stardivision.writer' =>
                        [
                            0 => 'sdw',
                            1 => 'vor',
                        ],
                    'application/vnd.stardivision.writer-global' =>
                        [
                            0 => 'sgl',
                        ],
                    'application/vnd.stepmania.package' =>
                        [
                            0 => 'smzip',
                        ],
                    'application/vnd.stepmania.stepchart' =>
                        [
                            0 => 'sm',
                        ],
                    'application/vnd.sun.xml.calc' =>
                        [
                            0 => 'sxc',
                        ],
                    'application/vnd.sun.xml.calc.template' =>
                        [
                            0 => 'stc',
                        ],
                    'application/vnd.sun.xml.draw' =>
                        [
                            0 => 'sxd',
                        ],
                    'application/vnd.sun.xml.draw.template' =>
                        [
                            0 => 'std',
                        ],
                    'application/vnd.sun.xml.impress' =>
                        [
                            0 => 'sxi',
                        ],
                    'application/vnd.sun.xml.impress.template' =>
                        [
                            0 => 'sti',
                        ],
                    'application/vnd.sun.xml.math' =>
                        [
                            0 => 'sxm',
                        ],
                    'application/vnd.sun.xml.writer' =>
                        [
                            0 => 'sxw',
                        ],
                    'application/vnd.sun.xml.writer.global' =>
                        [
                            0 => 'sxg',
                        ],
                    'application/vnd.sun.xml.writer.template' =>
                        [
                            0 => 'stw',
                        ],
                    'application/vnd.sus-calendar' =>
                        [
                            0 => 'sus',
                            1 => 'susp',
                        ],
                    'application/vnd.svd' =>
                        [
                            0 => 'svd',
                        ],
                    'application/vnd.symbian.install' =>
                        [
                            0 => 'sis',
                            1 => 'sisx',
                        ],
                    'application/vnd.syncml+xml' =>
                        [
                            0 => 'xsm',
                        ],
                    'application/vnd.syncml.dm+wbxml' =>
                        [
                            0 => 'bdm',
                        ],
                    'application/vnd.syncml.dm+xml' =>
                        [
                            0 => 'xdm',
                        ],
                    'application/vnd.tao.intent-module-archive' =>
                        [
                            0 => 'tao',
                        ],
                    'application/vnd.tcpdump.pcap' =>
                        [
                            0 => 'pcap',
                            1 => 'cap',
                            2 => 'dmp',
                        ],
                    'application/vnd.tmobile-livetv' =>
                        [
                            0 => 'tmo',
                        ],
                    'application/vnd.trid.tpt' =>
                        [
                            0 => 'tpt',
                        ],
                    'application/vnd.triscape.mxs' =>
                        [
                            0 => 'mxs',
                        ],
                    'application/vnd.trueapp' =>
                        [
                            0 => 'tra',
                        ],
                    'application/vnd.ufdl' =>
                        [
                            0 => 'ufd',
                            1 => 'ufdl',
                        ],
                    'application/vnd.uiq.theme' =>
                        [
                            0 => 'utz',
                        ],
                    'application/vnd.umajin' =>
                        [
                            0 => 'umj',
                        ],
                    'application/vnd.unity' =>
                        [
                            0 => 'unityweb',
                        ],
                    'application/vnd.uoml+xml' =>
                        [
                            0 => 'uoml',
                        ],
                    'application/vnd.vcx' =>
                        [
                            0 => 'vcx',
                        ],
                    'application/vnd.visio' =>
                        [
                            0 => 'vsd',
                            1 => 'vst',
                            2 => 'vss',
                            3 => 'vsw',
                        ],
                    'application/vnd.visionary' =>
                        [
                            0 => 'vis',
                        ],
                    'application/vnd.vsf' =>
                        [
                            0 => 'vsf',
                        ],
                    'application/vnd.wap.wbxml' =>
                        [
                            0 => 'wbxml',
                        ],
                    'application/vnd.wap.wmlc' =>
                        [
                            0 => 'wmlc',
                        ],
                    'application/vnd.wap.wmlscriptc' =>
                        [
                            0 => 'wmlsc',
                        ],
                    'application/vnd.webturbo' =>
                        [
                            0 => 'wtb',
                        ],
                    'application/vnd.wolfram.player' =>
                        [
                            0 => 'nbp',
                        ],
                    'application/vnd.wordperfect' =>
                        [
                            0 => 'wpd',
                        ],
                    'application/vnd.wqd' =>
                        [
                            0 => 'wqd',
                        ],
                    'application/vnd.wt.stf' =>
                        [
                            0 => 'stf',
                        ],
                    'application/vnd.xara' =>
                        [
                            0 => 'xar',
                        ],
                    'application/vnd.xfdl' =>
                        [
                            0 => 'xfdl',
                        ],
                    'application/vnd.yamaha.hv-dic' =>
                        [
                            0 => 'hvd',
                        ],
                    'application/vnd.yamaha.hv-script' =>
                        [
                            0 => 'hvs',
                        ],
                    'application/vnd.yamaha.hv-voice' =>
                        [
                            0 => 'hvp',
                        ],
                    'application/vnd.yamaha.openscoreformat' =>
                        [
                            0 => 'osf',
                        ],
                    'application/vnd.yamaha.openscoreformat.osfpvg+xml' =>
                        [
                            0 => 'osfpvg',
                        ],
                    'application/vnd.yamaha.smaf-audio' =>
                        [
                            0 => 'saf',
                        ],
                    'application/vnd.yamaha.smaf-phrase' =>
                        [
                            0 => 'spf',
                        ],
                    'application/vnd.yellowriver-custom-menu' =>
                        [
                            0 => 'cmp',
                        ],
                    'application/vnd.zul' =>
                        [
                            0 => 'zir',
                            1 => 'zirz',
                        ],
                    'application/vnd.zzazz.deck+xml' =>
                        [
                            0 => 'zaz',
                        ],
                    'application/voicexml+xml' =>
                        [
                            0 => 'vxml',
                        ],
                    'application/widget' =>
                        [
                            0 => 'wgt',
                        ],
                    'application/winhlp' =>
                        [
                            0 => 'hlp',
                        ],
                    'application/wsdl+xml' =>
                        [
                            0 => 'wsdl',
                        ],
                    'application/wspolicy+xml' =>
                        [
                            0 => 'wspolicy',
                        ],
                    'application/x-7z-compressed' =>
                        [
                            0 => '7z',
                        ],
                    'application/x-abiword' =>
                        [
                            0 => 'abw',
                        ],
                    'application/x-ace-compressed' =>
                        [
                            0 => 'ace',
                        ],
                    'application/x-apple-diskimage' =>
                        [
                            0 => 'dmg',
                        ],
                    'application/x-authorware-bin' =>
                        [
                            0 => 'aab',
                            1 => 'x32',
                            2 => 'u32',
                            3 => 'vox',
                        ],
                    'application/x-authorware-map' =>
                        [
                            0 => 'aam',
                        ],
                    'application/x-authorware-seg' =>
                        [
                            0 => 'aas',
                        ],
                    'application/x-bcpio' =>
                        [
                            0 => 'bcpio',
                        ],
                    'application/x-bittorrent' =>
                        [
                            0 => 'torrent',
                        ],
                    'application/x-blorb' =>
                        [
                            0 => 'blb',
                            1 => 'blorb',
                        ],
                    'application/x-bzip' =>
                        [
                            0 => 'bz',
                        ],
                    'application/x-bzip2' =>
                        [
                            0 => 'bz2',
                            1 => 'boz',
                        ],
                    'application/x-cbr' =>
                        [
                            0 => 'cbr',
                            1 => 'cba',
                            2 => 'cbt',
                            3 => 'cbz',
                            4 => 'cb7',
                        ],
                    'application/x-cdlink' =>
                        [
                            0 => 'vcd',
                        ],
                    'application/x-cfs-compressed' =>
                        [
                            0 => 'cfs',
                        ],
                    'application/x-chat' =>
                        [
                            0 => 'chat',
                        ],
                    'application/x-chess-pgn' =>
                        [
                            0 => 'pgn',
                        ],
                    'application/x-conference' =>
                        [
                            0 => 'nsc',
                        ],
                    'application/x-cpio' =>
                        [
                            0 => 'cpio',
                        ],
                    'application/x-csh' =>
                        [
                            0 => 'csh',
                        ],
                    'application/x-debian-package' =>
                        [
                            0 => 'deb',
                            1 => 'udeb',
                        ],
                    'application/x-dgc-compressed' =>
                        [
                            0 => 'dgc',
                        ],
                    'application/x-director' =>
                        [
                            0 => 'dir',
                            1 => 'dcr',
                            2 => 'dxr',
                            3 => 'cst',
                            4 => 'cct',
                            5 => 'cxt',
                            6 => 'w3d',
                            7 => 'fgd',
                            8 => 'swa',
                        ],
                    'application/x-doom' =>
                        [
                            0 => 'wad',
                        ],
                    'application/x-dtbncx+xml' =>
                        [
                            0 => 'ncx',
                        ],
                    'application/x-dtbook+xml' =>
                        [
                            0 => 'dtb',
                        ],
                    'application/x-dtbresource+xml' =>
                        [
                            0 => 'res',
                        ],
                    'application/x-dvi' =>
                        [
                            0 => 'dvi',
                        ],
                    'application/x-envoy' =>
                        [
                            0 => 'evy',
                        ],
                    'application/x-eva' =>
                        [
                            0 => 'eva',
                        ],
                    'application/x-font-bdf' =>
                        [
                            0 => 'bdf',
                        ],
                    'application/x-font-ghostscript' =>
                        [
                            0 => 'gsf',
                        ],
                    'application/x-font-linux-psf' =>
                        [
                            0 => 'psf',
                        ],
                    'application/x-font-pcf' =>
                        [
                            0 => 'pcf',
                        ],
                    'application/x-font-snf' =>
                        [
                            0 => 'snf',
                        ],
                    'application/x-font-type1' =>
                        [
                            0 => 'pfa',
                            1 => 'pfb',
                            2 => 'pfm',
                            3 => 'afm',
                        ],
                    'application/x-freearc' =>
                        [
                            0 => 'arc',
                        ],
                    'application/x-futuresplash' =>
                        [
                            0 => 'spl',
                        ],
                    'application/x-gca-compressed' =>
                        [
                            0 => 'gca',
                        ],
                    'application/x-glulx' =>
                        [
                            0 => 'ulx',
                        ],
                    'application/x-gnumeric' =>
                        [
                            0 => 'gnumeric',
                        ],
                    'application/x-gramps-xml' =>
                        [
                            0 => 'gramps',
                        ],
                    'application/x-gtar' =>
                        [
                            0 => 'gtar',
                        ],
                    'application/x-hdf' =>
                        [
                            0 => 'hdf',
                        ],
                    'application/x-install-instructions' =>
                        [
                            0 => 'install',
                        ],
                    'application/x-iso9660-image' =>
                        [
                            0 => 'iso',
                        ],
                    'application/x-java-jnlp-file' =>
                        [
                            0 => 'jnlp',
                        ],
                    'application/x-latex' =>
                        [
                            0 => 'latex',
                        ],
                    'application/x-lzh-compressed' =>
                        [
                            0 => 'lzh',
                            1 => 'lha',
                        ],
                    'application/x-mie' =>
                        [
                            0 => 'mie',
                        ],
                    'application/x-mobipocket-ebook' =>
                        [
                            0 => 'prc',
                            1 => 'mobi',
                        ],
                    'application/x-ms-application' =>
                        [
                            0 => 'application',
                        ],
                    'application/x-ms-shortcut' =>
                        [
                            0 => 'lnk',
                        ],
                    'application/x-ms-wmd' =>
                        [
                            0 => 'wmd',
                        ],
                    'application/x-ms-wmz' =>
                        [
                            0 => 'wmz',
                        ],
                    'application/x-ms-xbap' =>
                        [
                            0 => 'xbap',
                        ],
                    'application/x-msaccess' =>
                        [
                            0 => 'mdb',
                        ],
                    'application/x-msbinder' =>
                        [
                            0 => 'obd',
                        ],
                    'application/x-mscardfile' =>
                        [
                            0 => 'crd',
                        ],
                    'application/x-msclip' =>
                        [
                            0 => 'clp',
                        ],
                    'application/x-msdownload' =>
                        [
                            0 => 'exe',
                            1 => 'dll',
                            2 => 'com',
                            3 => 'bat',
                            4 => 'msi',
                        ],
                    'application/x-msmediaview' =>
                        [
                            0 => 'mvb',
                            1 => 'm13',
                            2 => 'm14',
                        ],
                    'application/x-msmetafile' =>
                        [
                            0 => 'wmf',
                            1 => 'wmz',
                            2 => 'emf',
                            3 => 'emz',
                        ],
                    'application/x-msmoney' =>
                        [
                            0 => 'mny',
                        ],
                    'application/x-mspublisher' =>
                        [
                            0 => 'pub',
                        ],
                    'application/x-msschedule' =>
                        [
                            0 => 'scd',
                        ],
                    'application/x-msterminal' =>
                        [
                            0 => 'trm',
                        ],
                    'application/x-mswrite' =>
                        [
                            0 => 'wri',
                        ],
                    'application/x-netcdf' =>
                        [
                            0 => 'nc',
                            1 => 'cdf',
                        ],
                    'application/x-nzb' =>
                        [
                            0 => 'nzb',
                        ],
                    'application/x-pkcs12' =>
                        [
                            0 => 'p12',
                            1 => 'pfx',
                        ],
                    'application/x-pkcs7-certificates' =>
                        [
                            0 => 'p7b',
                            1 => 'spc',
                        ],
                    'application/x-pkcs7-certreqresp' =>
                        [
                            0 => 'p7r',
                        ],
                    'application/x-rar-compressed' =>
                        [
                            0 => 'rar',
                        ],
                    'application/x-research-info-systems' =>
                        [
                            0 => 'ris',
                        ],
                    'application/x-sh' =>
                        [
                            0 => 'sh',
                        ],
                    'application/x-shar' =>
                        [
                            0 => 'shar',
                        ],
                    'application/x-shockwave-flash' =>
                        [
                            0 => 'swf',
                        ],
                    'application/x-silverlight-app' =>
                        [
                            0 => 'xap',
                        ],
                    'application/x-sql' =>
                        [
                            0 => 'sql',
                        ],
                    'application/x-stuffit' =>
                        [
                            0 => 'sit',
                        ],
                    'application/x-stuffitx' =>
                        [
                            0 => 'sitx',
                        ],
                    'application/x-subrip' =>
                        [
                            0 => 'srt',
                        ],
                    'application/x-sv4cpio' =>
                        [
                            0 => 'sv4cpio',
                        ],
                    'application/x-sv4crc' =>
                        [
                            0 => 'sv4crc',
                        ],
                    'application/x-t3vm-image' =>
                        [
                            0 => 't3',
                        ],
                    'application/x-tads' =>
                        [
                            0 => 'gam',
                        ],
                    'application/x-tar' =>
                        [
                            0 => 'tar',
                        ],
                    'application/x-tcl' =>
                        [
                            0 => 'tcl',
                        ],
                    'application/x-tex' =>
                        [
                            0 => 'tex',
                        ],
                    'application/x-tex-tfm' =>
                        [
                            0 => 'tfm',
                        ],
                    'application/x-texinfo' =>
                        [
                            0 => 'texinfo',
                            1 => 'texi',
                        ],
                    'application/x-tgif' =>
                        [
                            0 => 'obj',
                        ],
                    'application/x-ustar' =>
                        [
                            0 => 'ustar',
                        ],
                    'application/x-wais-source' =>
                        [
                            0 => 'src',
                        ],
                    'application/x-x509-ca-cert' =>
                        [
                            0 => 'der',
                            1 => 'crt',
                        ],
                    'application/x-xfig' =>
                        [
                            0 => 'fig',
                        ],
                    'application/x-xliff+xml' =>
                        [
                            0 => 'xlf',
                        ],
                    'application/x-xpinstall' =>
                        [
                            0 => 'xpi',
                        ],
                    'application/x-xz' =>
                        [
                            0 => 'xz',
                        ],
                    'application/x-zmachine' =>
                        [
                            0 => 'z1',
                            1 => 'z2',
                            2 => 'z3',
                            3 => 'z4',
                            4 => 'z5',
                            5 => 'z6',
                            6 => 'z7',
                            7 => 'z8',
                        ],
                    'application/xaml+xml' =>
                        [
                            0 => 'xaml',
                        ],
                    'application/xcap-diff+xml' =>
                        [
                            0 => 'xdf',
                        ],
                    'application/xenc+xml' =>
                        [
                            0 => 'xenc',
                        ],
                    'application/xhtml+xml' =>
                        [
                            0 => 'xhtml',
                            1 => 'xht',
                        ],
                    'application/xml' =>
                        [
                            0 => 'xml',
                            1 => 'xsl',
                        ],
                    'application/xml-dtd' =>
                        [
                            0 => 'dtd',
                        ],
                    'application/xop+xml' =>
                        [
                            0 => 'xop',
                        ],
                    'application/xproc+xml' =>
                        [
                            0 => 'xpl',
                        ],
                    'application/xslt+xml' =>
                        [
                            0 => 'xslt',
                        ],
                    'application/xspf+xml' =>
                        [
                            0 => 'xspf',
                        ],
                    'application/xv+xml' =>
                        [
                            0 => 'mxml',
                            1 => 'xhvml',
                            2 => 'xvml',
                            3 => 'xvm',
                        ],
                    'application/yang' =>
                        [
                            0 => 'yang',
                        ],
                    'application/yin+xml' =>
                        [
                            0 => 'yin',
                        ],
                    'application/zip' =>
                        [
                            0 => 'zip',
                        ],
                    'audio/adpcm' =>
                        [
                            0 => 'adp',
                        ],
                    'audio/basic' =>
                        [
                            0 => 'au',
                            1 => 'snd',
                        ],
                    'audio/midi' =>
                        [
                            0 => 'mid',
                            1 => 'midi',
                            2 => 'kar',
                            3 => 'rmi',
                        ],
                    'audio/mp4' =>
                        [
                            0 => 'm4a',
                            1 => 'mp4a',
                        ],
                    'audio/ogg' =>
                        [
                            0 => 'oga',
                            1 => 'ogg',
                            2 => 'spx',
                        ],
                    'audio/s3m' =>
                        [
                            0 => 's3m',
                        ],
                    'audio/silk' =>
                        [
                            0 => 'sil',
                        ],
                    'audio/vnd.dece.audio' =>
                        [
                            0 => 'uva',
                            1 => 'uvva',
                        ],
                    'audio/vnd.digital-winds' =>
                        [
                            0 => 'eol',
                        ],
                    'audio/vnd.dra' =>
                        [
                            0 => 'dra',
                        ],
                    'audio/vnd.dts' =>
                        [
                            0 => 'dts',
                        ],
                    'audio/vnd.dts.hd' =>
                        [
                            0 => 'dtshd',
                        ],
                    'audio/vnd.lucent.voice' =>
                        [
                            0 => 'lvp',
                        ],
                    'audio/vnd.ms-playready.media.pya' =>
                        [
                            0 => 'pya',
                        ],
                    'audio/vnd.nuera.ecelp4800' =>
                        [
                            0 => 'ecelp4800',
                        ],
                    'audio/vnd.nuera.ecelp7470' =>
                        [
                            0 => 'ecelp7470',
                        ],
                    'audio/vnd.nuera.ecelp9600' =>
                        [
                            0 => 'ecelp9600',
                        ],
                    'audio/vnd.rip' =>
                        [
                            0 => 'rip',
                        ],
                    'audio/webm' =>
                        [
                            0 => 'weba',
                        ],
                    'audio/x-aac' =>
                        [
                            0 => 'aac',
                        ],
                    'audio/x-aiff' =>
                        [
                            0 => 'aif',
                            1 => 'aiff',
                            2 => 'aifc',
                        ],
                    'audio/x-caf' =>
                        [
                            0 => 'caf',
                        ],
                    'audio/x-flac' =>
                        [
                            0 => 'flac',
                        ],
                    'audio/x-matroska' =>
                        [
                            0 => 'mka',
                        ],
                    'audio/x-mpegurl' =>
                        [
                            0 => 'm3u',
                        ],
                    'audio/x-ms-wax' =>
                        [
                            0 => 'wax',
                        ],
                    'audio/x-ms-wma' =>
                        [
                            0 => 'wma',
                        ],
                    'audio/x-pn-realaudio' =>
                        [
                            0 => 'ram',
                            1 => 'ra',
                        ],
                    'audio/x-pn-realaudio-plugin' =>
                        [
                            0 => 'rmp',
                        ],
                    'audio/x-wav' =>
                        [
                            0 => 'wav',
                        ],
                    'audio/xm' =>
                        [
                            0 => 'xm',
                        ],
                    'chemical/x-cdx' =>
                        [
                            0 => 'cdx',
                        ],
                    'chemical/x-cif' =>
                        [
                            0 => 'cif',
                        ],
                    'chemical/x-cmdf' =>
                        [
                            0 => 'cmdf',
                        ],
                    'chemical/x-cml' =>
                        [
                            0 => 'cml',
                        ],
                    'chemical/x-csml' =>
                        [
                            0 => 'csml',
                        ],
                    'chemical/x-xyz' =>
                        [
                            0 => 'xyz',
                        ],
                    'font/collection' =>
                        [
                            0 => 'ttc',
                        ],
                    'font/otf' =>
                        [
                            0 => 'otf',
                        ],
                    'font/ttf' =>
                        [
                            0 => 'ttf',
                        ],
                    'font/woff' =>
                        [
                            0 => 'woff',
                        ],
                    'font/woff2' =>
                        [
                            0 => 'woff2',
                        ],
                    'image/bmp' =>
                        [
                            0 => 'bmp',
                        ],
                    'image/cgm' =>
                        [
                            0 => 'cgm',
                        ],
                    'image/g3fax' =>
                        [
                            0 => 'g3',
                        ],
                    'image/gif' =>
                        [
                            0 => 'gif',
                        ],
                    'image/ief' =>
                        [
                            0 => 'ief',
                        ],
                    'image/ktx' =>
                        [
                            0 => 'ktx',
                        ],
                    'image/png' =>
                        [
                            0 => 'png',
                        ],
                    'image/prs.btif' =>
                        [
                            0 => 'btif',
                        ],
                    'image/sgi' =>
                        [
                            0 => 'sgi',
                        ],
                    'image/svg+xml' =>
                        [
                            0 => 'svg',
                            1 => 'svgz',
                        ],
                    'image/tiff' =>
                        [
                            0 => 'tiff',
                            1 => 'tif',
                        ],
                    'image/vnd.adobe.photoshop' =>
                        [
                            0 => 'psd',
                        ],
                    'image/vnd.dece.graphic' =>
                        [
                            0 => 'uvi',
                            1 => 'uvvi',
                            2 => 'uvg',
                            3 => 'uvvg',
                        ],
                    'image/vnd.djvu' =>
                        [
                            0 => 'djvu',
                            1 => 'djv',
                        ],
                    'image/vnd.dvb.subtitle' =>
                        [
                            0 => 'sub',
                        ],
                    'image/vnd.dwg' =>
                        [
                            0 => 'dwg',
                        ],
                    'image/vnd.dxf' =>
                        [
                            0 => 'dxf',
                        ],
                    'image/vnd.fastbidsheet' =>
                        [
                            0 => 'fbs',
                        ],
                    'image/vnd.fpx' =>
                        [
                            0 => 'fpx',
                        ],
                    'image/vnd.fst' =>
                        [
                            0 => 'fst',
                        ],
                    'image/vnd.fujixerox.edmics-mmr' =>
                        [
                            0 => 'mmr',
                        ],
                    'image/vnd.fujixerox.edmics-rlc' =>
                        [
                            0 => 'rlc',
                        ],
                    'image/vnd.ms-modi' =>
                        [
                            0 => 'mdi',
                        ],
                    'image/vnd.ms-photo' =>
                        [
                            0 => 'wdp',
                        ],
                    'image/vnd.net-fpx' =>
                        [
                            0 => 'npx',
                        ],
                    'image/vnd.wap.wbmp' =>
                        [
                            0 => 'wbmp',
                        ],
                    'image/vnd.xiff' =>
                        [
                            0 => 'xif',
                        ],
                    'image/webp' =>
                        [
                            0 => 'webp',
                        ],
                    'image/x-3ds' =>
                        [
                            0 => '3ds',
                        ],
                    'image/x-cmu-raster' =>
                        [
                            0 => 'ras',
                        ],
                    'image/x-cmx' =>
                        [
                            0 => 'cmx',
                        ],
                    'image/x-freehand' =>
                        [
                            0 => 'fh',
                            1 => 'fhc',
                            2 => 'fh4',
                            3 => 'fh5',
                            4 => 'fh7',
                        ],
                    'image/x-icon' =>
                        [
                            0 => 'ico',
                        ],
                    'image/x-mrsid-image' =>
                        [
                            0 => 'sid',
                        ],
                    'image/x-pcx' =>
                        [
                            0 => 'pcx',
                        ],
                    'image/x-pict' =>
                        [
                            0 => 'pic',
                            1 => 'pct',
                        ],
                    'image/x-portable-anymap' =>
                        [
                            0 => 'pnm',
                        ],
                    'image/x-portable-bitmap' =>
                        [
                            0 => 'pbm',
                        ],
                    'image/x-portable-graymap' =>
                        [
                            0 => 'pgm',
                        ],
                    'image/x-portable-pixmap' =>
                        [
                            0 => 'ppm',
                        ],
                    'image/x-rgb' =>
                        [
                            0 => 'rgb',
                        ],
                    'image/x-tga' =>
                        [
                            0 => 'tga',
                        ],
                    'image/x-xbitmap' =>
                        [
                            0 => 'xbm',
                        ],
                    'image/x-xpixmap' =>
                        [
                            0 => 'xpm',
                        ],
                    'image/x-xwindowdump' =>
                        [
                            0 => 'xwd',
                        ],
                    'message/rfc822' =>
                        [
                            0 => 'eml',
                            1 => 'mime',
                        ],
                    'model/iges' =>
                        [
                            0 => 'igs',
                            1 => 'iges',
                        ],
                    'model/mesh' =>
                        [
                            0 => 'msh',
                            1 => 'mesh',
                            2 => 'silo',
                        ],
                    'model/vnd.collada+xml' =>
                        [
                            0 => 'dae',
                        ],
                    'model/vnd.dwf' =>
                        [
                            0 => 'dwf',
                        ],
                    'model/vnd.gdl' =>
                        [
                            0 => 'gdl',
                        ],
                    'model/vnd.gtw' =>
                        [
                            0 => 'gtw',
                        ],
                    'model/vnd.mts' =>
                        [
                            0 => 'mts',
                        ],
                    'model/vnd.vtu' =>
                        [
                            0 => 'vtu',
                        ],
                    'model/vrml' =>
                        [
                            0 => 'wrl',
                            1 => 'vrml',
                        ],
                    'model/x3d+binary' =>
                        [
                            0 => 'x3db',
                            1 => 'x3dbz',
                        ],
                    'model/x3d+vrml' =>
                        [
                            0 => 'x3dv',
                            1 => 'x3dvz',
                        ],
                    'model/x3d+xml' =>
                        [
                            0 => 'x3d',
                            1 => 'x3dz',
                        ],
                    'text/cache-manifest' =>
                        [
                            0 => 'appcache',
                        ],
                    'text/calendar' =>
                        [
                            0 => 'ics',
                            1 => 'ifb',
                        ],
                    'text/css' =>
                        [
                            0 => 'css',
                        ],
                    'text/csv' =>
                        [
                            0 => 'csv',
                        ],
                    'text/html' =>
                        [
                            0 => 'html',
                            1 => 'htm',
                        ],
                    'text/n3' =>
                        [
                            0 => 'n3',
                        ],
                    'text/plain' =>
                        [
                            0 => 'txt',
                            1 => 'text',
                            2 => 'conf',
                            3 => 'def',
                            4 => 'list',
                            5 => 'log',
                            6 => 'in',
                        ],
                    'text/prs.lines.tag' =>
                        [
                            0 => 'dsc',
                        ],
                    'text/richtext' =>
                        [
                            0 => 'rtx',
                        ],
                    'text/sgml' =>
                        [
                            0 => 'sgml',
                            1 => 'sgm',
                        ],
                    'text/tab-separated-values' =>
                        [
                            0 => 'tsv',
                        ],
                    'text/troff' =>
                        [
                            0 => 't',
                            1 => 'tr',
                            2 => 'roff',
                            3 => 'man',
                            4 => 'me',
                            5 => 'ms',
                        ],
                    'text/turtle' =>
                        [
                            0 => 'ttl',
                        ],
                    'text/uri-list' =>
                        [
                            0 => 'uri',
                            1 => 'uris',
                            2 => 'urls',
                        ],
                    'text/vcard' =>
                        [
                            0 => 'vcard',
                        ],
                    'text/vnd.curl' =>
                        [
                            0 => 'curl',
                        ],
                    'text/vnd.curl.dcurl' =>
                        [
                            0 => 'dcurl',
                        ],
                    'text/vnd.curl.mcurl' =>
                        [
                            0 => 'mcurl',
                        ],
                    'text/vnd.curl.scurl' =>
                        [
                            0 => 'scurl',
                        ],
                    'text/vnd.dvb.subtitle' =>
                        [
                            0 => 'sub',
                        ],
                    'text/vnd.fly' =>
                        [
                            0 => 'fly',
                        ],
                    'text/vnd.fmi.flexstor' =>
                        [
                            0 => 'flx',
                        ],
                    'text/vnd.graphviz' =>
                        [
                            0 => 'gv',
                        ],
                    'text/vnd.in3d.3dml' =>
                        [
                            0 => '3dml',
                        ],
                    'text/vnd.in3d.spot' =>
                        [
                            0 => 'spot',
                        ],
                    'text/vnd.sun.j2me.app-descriptor' =>
                        [
                            0 => 'jad',
                        ],
                    'text/vnd.wap.wml' =>
                        [
                            0 => 'wml',
                        ],
                    'text/vnd.wap.wmlscript' =>
                        [
                            0 => 'wmls',
                        ],
                    'text/x-asm' =>
                        [
                            0 => 's',
                            1 => 'asm',
                        ],
                    'text/x-c' =>
                        [
                            0 => 'c',
                            1 => 'cc',
                            2 => 'cxx',
                            3 => 'cpp',
                            4 => 'h',
                            5 => 'hh',
                            6 => 'dic',
                        ],
                    'text/x-fortran' =>
                        [
                            0 => 'f',
                            1 => 'for',
                            2 => 'f77',
                            3 => 'f90',
                        ],
                    'text/x-java-source' =>
                        [
                            0 => 'java',
                        ],
                    'text/x-nfo' =>
                        [
                            0 => 'nfo',
                        ],
                    'text/x-opml' =>
                        [
                            0 => 'opml',
                        ],
                    'text/x-pascal' =>
                        [
                            0 => 'p',
                            1 => 'pas',
                        ],
                    'text/x-setext' =>
                        [
                            0 => 'etx',
                        ],
                    'text/x-sfv' =>
                        [
                            0 => 'sfv',
                        ],
                    'text/x-uuencode' =>
                        [
                            0 => 'uu',
                        ],
                    'text/x-vcalendar' =>
                        [
                            0 => 'vcs',
                        ],
                    'text/x-vcard' =>
                        [
                            0 => 'vcf',
                        ],
                    'video/3gpp' =>
                        [
                            0 => '3gp',
                        ],
                    'video/3gpp2' =>
                        [
                            0 => '3g2',
                        ],
                    'video/h261' =>
                        [
                            0 => 'h261',
                        ],
                    'video/h263' =>
                        [
                            0 => 'h263',
                        ],
                    'video/h264' =>
                        [
                            0 => 'h264',
                        ],
                    'video/jpeg' =>
                        [
                            0 => 'jpgv',
                        ],
                    'video/jpm' =>
                        [
                            0 => 'jpm',
                            1 => 'jpgm',
                        ],
                    'video/mj2' =>
                        [
                            0 => 'mj2',
                            1 => 'mjp2',
                        ],
                    'video/mp4' =>
                        [
                            0 => 'mp4',
                            1 => 'mp4v',
                            2 => 'mpg4',
                        ],
                    'video/mpeg' =>
                        [
                            0 => 'mpeg',
                            1 => 'mpg',
                            2 => 'mpe',
                            3 => 'm1v',
                            4 => 'm2v',
                        ],
                    'video/ogg' =>
                        [
                            0 => 'ogv',
                        ],
                    'video/quicktime' =>
                        [
                            0 => 'qt',
                            1 => 'mov',
                        ],
                    'video/vnd.dece.hd' =>
                        [
                            0 => 'uvh',
                            1 => 'uvvh',
                        ],
                    'video/vnd.dece.mobile' =>
                        [
                            0 => 'uvm',
                            1 => 'uvvm',
                        ],
                    'video/vnd.dece.pd' =>
                        [
                            0 => 'uvp',
                            1 => 'uvvp',
                        ],
                    'video/vnd.dece.sd' =>
                        [
                            0 => 'uvs',
                            1 => 'uvvs',
                        ],
                    'video/vnd.dece.video' =>
                        [
                            0 => 'uvv',
                            1 => 'uvvv',
                        ],
                    'video/vnd.dvb.file' =>
                        [
                            0 => 'dvb',
                        ],
                    'video/vnd.fvt' =>
                        [
                            0 => 'fvt',
                        ],
                    'video/vnd.mpegurl' =>
                        [
                            0 => 'mxu',
                            1 => 'm4u',
                        ],
                    'video/vnd.ms-playready.media.pyv' =>
                        [
                            0 => 'pyv',
                        ],
                    'video/vnd.uvvu.mp4' =>
                        [
                            0 => 'uvu',
                            1 => 'uvvu',
                        ],
                    'video/vnd.vivo' =>
                        [
                            0 => 'viv',
                        ],
                    'video/webm' =>
                        [
                            0 => 'webm',
                        ],
                    'video/x-f4v' =>
                        [
                            0 => 'f4v',
                        ],
                    'video/x-fli' =>
                        [
                            0 => 'fli',
                        ],
                    'video/x-flv' =>
                        [
                            0 => 'flv',
                        ],
                    'video/x-m4v' =>
                        [
                            0 => 'm4v',
                        ],
                    'video/x-matroska' =>
                        [
                            0 => 'mkv',
                            1 => 'mk3d',
                            2 => 'mks',
                        ],
                    'video/x-mng' =>
                        [
                            0 => 'mng',
                        ],
                    'video/x-ms-asf' =>
                        [
                            0 => 'asf',
                            1 => 'asx',
                        ],
                    'video/x-ms-vob' =>
                        [
                            0 => 'vob',
                        ],
                    'video/x-ms-wm' =>
                        [
                            0 => 'wm',
                        ],
                    'video/x-ms-wmv' =>
                        [
                            0 => 'wmv',
                        ],
                    'video/x-ms-wmx' =>
                        [
                            0 => 'wmx',
                        ],
                    'video/x-ms-wvx' =>
                        [
                            0 => 'wvx',
                        ],
                    'video/x-msvideo' =>
                        [
                            0 => 'avi',
                        ],
                    'video/x-sgi-movie' =>
                        [
                            0 => 'movie',
                        ],
                    'video/x-smv' =>
                        [
                            0 => 'smv',
                        ],
                    'x-conference/x-cooltalk' =>
                        [
                            0 => 'ice',
                        ],
                ],
        ];
    }

    private static function _clean($input)
    {
        return strtolower(trim($input));
    }
}
