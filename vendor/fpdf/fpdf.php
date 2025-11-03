<?php
/*
 * FPDF version 1.81
 *
 * Copyright (C) 2001-2015 Olivier Plathey
 */

define('FPDF_VERSION', '1.81');

class FPDF
{
    protected $page;               // current page number
    protected $n;                  // current object number
    protected $offsets;            // array of object offsets
    protected $buffer;             // buffer holding in-memory PDF
    protected $pages;              // array containing pages
    protected $state;              // current document state
    protected $compress;           // compression flag
    protected $k;                  // scale factor (number of points in user unit)
    protected $DefOrientation;     // default orientation
    protected $CurOrientation;     // current orientation
    protected $StdPageSizes;       // standard page sizes
    protected $DefPageSize;        // default page size
    protected $CurPageSize;        // current page size
    protected $PageSizes;          // used for pages with non default sizes or orientations
    protected $wPt, $hPt;          // dimensions of current page in points
    protected $w, $h;              // dimensions of current page in user unit
    protected $lMargin;            // left margin
    protected $tMargin;            // top margin
    protected $rMargin;            // right margin
    protected $bMargin;            // page break margin
    protected $cMargin;            // cell margin
    protected $x, $y;              // current position in user unit
    protected $lasth;              // height of last printed cell
    protected $LineWidth;          // line width in user unit
    protected $fontpath;           // path containing fonts
    protected $CoreFonts;          // array of core font names
    protected $fonts;              // array of used fonts
    protected $FontFiles;          // array of font files
    protected $diffs;              // array of encoding differences
    protected $FontFamily;         // current font family
    protected $FontStyle;          // current font style
    protected $underline;          // underlining flag
    protected $CurrentFont;        // current font info
    protected $FontSizePt;         // current font size in points
    protected $FontSize;           // current font size in user unit
    protected $DrawColor;          // commands for drawing color
    protected $FillColor;          // commands for filling color
    protected $TextColor;          // commands for text color
    protected $ColorFlag;          // indicates whether fill and text colors are different
    protected $ws;                 // word spacing
    protected $images;             // array of used images
    protected $PageLinks;          // array of links in pages
    protected $links;              // array of internal links
    protected $AutoPageBreak;      // automatic page breaking
    protected $PageBreakTrigger;   // threshold used to trigger page breaks
    protected $InHeader;           // flag set when processing header
    protected $InFooter;           // flag set when processing footer
    protected $ZoomMode;           // zoom display mode
    protected $LayoutMode;         // layout display mode
    protected $title;              // title
    protected $subject;            // subject
    protected $author;             // author
    protected $keywords;           // keywords
    protected $creator;            // creator
    protected $AliasNbPages;       // alias for total number of pages
    protected $PDFVersion;         // PDF version number

    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        $this->_dochecks();
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->pages = [];
        $this->PageSizes = [];
        $this->state = 0;
        $this->fonts = [];
        $this->FontFiles = [];
        $this->diffs = [];
        $this->images = [];
        $this->links = [];
        $this->InHeader = false;
        $this->InFooter = false;
        $this->lasth = 0;
        $this->FontFamily = '';
        $this->FontStyle = '';
        $this->FontSizePt = 12;
        $this->underline = false;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->ws = 0;
        $this->images = [];
        $this->AutoPageBreak = true;
        $this->PageBreakTrigger = 0;
        $this->AliasNbPages = '{nb}';
        $this->PDFVersion = '1.3';

        $this->CoreFonts = [
            'courier' => 'Courier',
            'courierB' => 'Courier-Bold',
            'courierI' => 'Courier-Oblique',
            'courierBI' => 'Courier-BoldOblique',
            'helvetica' => 'Helvetica',
            'helveticaB' => 'Helvetica-Bold',
            'helveticaI' => 'Helvetica-Oblique',
            'helveticaBI' => 'Helvetica-BoldOblique',
            'times' => 'Times-Roman',
            'timesB' => 'Times-Bold',
            'timesI' => 'Times-Italic',
            'timesBI' => 'Times-BoldItalic',
            'symbol' => 'Symbol',
            'zapfdingbats' => 'ZapfDingbats'
        ];

        $this->fontpath = __DIR__ . '/font/';

        if (!file_exists($this->fontpath)) {
            $this->fontpath = '';
        }

        $this->SetMargins(10, 10);
        $this->SetAutoPageBreak(true, 10);
        $this->SetDisplayMode('default');
        $this->SetCompression(true);
        $this->SetTitle('');
        $this->SetAuthor('');
        $this->SetSubject('');
        $this->SetKeywords('');
        $this->SetCreator('');

        $orientation = strtoupper($orientation);
        if ($orientation === 'P' || $orientation === 'PORTRAIT') {
            $orientation = 'P';
        } elseif ($orientation === 'L' || $orientation === 'LANDSCAPE') {
            $orientation = 'L';
        } else {
            $this->Error('Incorrect orientation: ' . $orientation);
        }
        $this->DefOrientation = $orientation;
        $this->CurOrientation = $orientation;

        $this->StdPageSizes = [
            'a3' => [841.89, 1190.55],
            'a4' => [595.28, 841.89],
            'a5' => [420.94, 595.28],
            'letter' => [612, 792],
            'legal' => [612, 1008]
        ];

        $size = $this->_getpagesize($size);
        $this->DefPageSize = $size;
        $this->CurPageSize = $size;

        $unit = strtolower($unit);
        if ($unit === 'pt') {
            $this->k = 1;
        } elseif ($unit === 'mm') {
            $this->k = 72 / 25.4;
        } elseif ($unit === 'cm') {
            $this->k = 72 / 2.54;
        } elseif ($unit === 'in') {
            $this->k = 72;
        } else {
            $this->Error('Incorrect unit: ' . $unit);
        }

        $this->w = $this->CurPageSize[0] / $this->k;
        $this->h = $this->CurPageSize[1] / $this->k;
        $this->wPt = $this->CurPageSize[0];
        $this->hPt = $this->CurPageSize[1];
    }

    protected function _dochecks()
    {
        if (ini_get('mbstring.func_overload') & 2) {
            $this->Error('mbstring overloading must be disabled');
        }

        if (get_magic_quotes_runtime()) {
            @set_magic_quotes_runtime(0);
        }
    }

    protected function _getpagesize($size)
    {
        if (is_string($size)) {
            $size = strtolower($size);
            if (!isset($this->StdPageSizes[$size])) {
                $this->Error('Unknown page size: ' . $size);
            }
            $a = $this->StdPageSizes[$size];
            return [$a[0], $a[1]];
        } elseif (is_array($size)) {
            if (count($size) != 2) {
                $this->Error('Invalid page size: ' . implode(' ', $size));
            }
            return [$size[0] * $this->k, $size[1] * $this->k];
        } else {
            $this->Error('Invalid page size');
        }
    }

    public function SetMargins($left, $top, $right = null)
    {
        $this->lMargin = $left;
        $this->tMargin = $top;
        if ($right === null) {
            $right = $left;
        }
        $this->rMargin = $right;
    }

    public function SetAutoPageBreak($auto, $margin = 0)
    {
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
        $this->PageBreakTrigger = $this->h - $margin;
    }

    public function SetDisplayMode($zoom, $layout = 'default')
    {
        if ($zoom == 'fullpage' || $zoom == 'fullwidth' || $zoom == 'real' || $zoom == 'default' || !is_string($zoom)) {
            $this->ZoomMode = $zoom;
        } else {
            $this->Error('Incorrect zoom display mode: ' . $zoom);
        }
        if ($layout == 'single' || $layout == 'continuous' || $layout == 'two' || $layout == 'default') {
            $this->LayoutMode = $layout;
        } else {
            $this->Error('Incorrect layout display mode: ' . $layout);
        }
    }

    public function SetCompression($compress)
    {
        if (function_exists('gzcompress')) {
            $this->compress = $compress;
        } else {
            $this->compress = false;
        }
    }

    public function SetTitle($title, $isUTF8 = false)
    {
        $this->title = $isUTF8 ? $title : utf8_encode($title);
    }

    public function SetAuthor($author, $isUTF8 = false)
    {
        $this->author = $isUTF8 ? $author : utf8_encode($author);
    }

    public function SetSubject($subject, $isUTF8 = false)
    {
        $this->subject = $isUTF8 ? $subject : utf8_encode($subject);
    }

    public function SetKeywords($keywords, $isUTF8 = false)
    {
        $this->keywords = $isUTF8 ? $keywords : utf8_encode($keywords);
    }

    public function SetCreator($creator, $isUTF8 = false)
    {
        $this->creator = $isUTF8 ? $creator : utf8_encode($creator);
    }

    public function AliasNbPages($alias = '{nb}')
    {
        $this->AliasNbPages = $alias;
    }

    public function AddPage($orientation = '', $size = '')
    {
        if ($this->state == 0) {
            $this->Open();
        }
        $family = $this->FontFamily;
        $style = $this->FontStyle;
        $fontsize = $this->FontSizePt;
        $lw = $this->LineWidth;
        $dc = $this->DrawColor;
        $fc = $this->FillColor;
        $tc = $this->TextColor;
        $cf = $this->ColorFlag;
        if ($this->page > 0) {
            $this->InFooter = true;
            $this->Footer();
            $this->InFooter = false;
            $this->_endpage();
        }
        $this->_beginpage($orientation, $size);
        $this->_setupfonts($family, $style, $fontsize);
        $this->_setupdrawcolor($dc);
        $this->_setupfillcolor($fc);
        $this->_setuptextcolor($tc, $cf);
        $this->_setlinewidth($lw);

        $this->Header();
        $this->SetY($this->tMargin);
    }

    protected function _setupfonts($family, $style, $fontsize)
    {
        if ($family) {
            $this->SetFont($family, $style, $fontsize);
        }
    }

    protected function _setupdrawcolor($dc)
    {
        if ($dc != '0 G') {
            $this->DrawColor = $dc;
            $this->_out($dc);
        }
    }

    protected function _setupfillcolor($fc)
    {
        if ($fc != '0 g') {
            $this->FillColor = $fc;
            $this->_out($fc);
        }
    }

    protected function _setuptextcolor($tc, $cf)
    {
        if ($tc != '0 g') {
            $this->TextColor = $tc;
            $this->ColorFlag = $cf;
        }
    }

    protected function _setlinewidth($lw)
    {
        if ($this->LineWidth != $lw) {
            $this->LineWidth = $lw;
            $this->_out(sprintf('%.2F w', $lw * $this->k));
        }
    }

    public function Header()
    {
        // override in subclass
    }

    public function Footer()
    {
        // override in subclass
    }

    public function SetFont($family, $style = '', $size = 0)
    {
        $family = strtolower($family);
        if ($family == 'arial') {
            $family = 'helvetica';
        }
        if ($family == 'symbol' || $family == 'zapfdingbats') {
            $style = '';
        }
        $style = strtoupper($style);
        $style = str_replace(' ', '', $style);
        $style = str_replace('U', '', $style);
        if ($family == '') {
            $family = $this->FontFamily;
        }
        if ($size == 0) {
            $size = $this->FontSizePt;
        }
        if ($this->FontFamily == $family && $this->FontStyle == $style && $this->FontSizePt == $size) {
            return;
        }
        $fontkey = $family . $style;
        $this->_addfont($family, $style);
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
        $this->CurrentFont = $this->fonts[$fontkey];
        if ($this->page > 0) {
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
        }
    }

    protected function _addfont($family, $style)
    {
        $fontkey = $family . $style;
        if (isset($this->fonts[$fontkey])) {
            return;
        }
        if (isset($this->CoreFonts[$fontkey])) {
            $font = ['type' => 'core', 'name' => $this->CoreFonts[$fontkey]];
        } else {
            $this->Error('Undefined font: ' . $family . ' ' . $style);
        }
        $font['i'] = count($this->fonts) + 1;
        $this->fonts[$fontkey] = $font;
    }

    public function SetFontSize($size)
    {
        if ($this->FontSizePt == $size) {
            return;
        }
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
        if ($this->page > 0) {
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
        }
    }

    public function AddLink()
    {
        $n = count($this->links) + 1;
        $this->links[$n] = [0, 0];
        return $n;
    }

    public function SetLink($link, $y = 0, $page = -1)
    {
        if ($y == -1) {
            $y = $this->y;
        }
        if ($page == -1) {
            $page = $this->page;
        }
        $this->links[$link] = [$page, $y];
    }

    public function Text($x, $y, $txt)
    {
        if ($this->CurrentFont === null) {
            $this->Error('No font has been set');
        }
        $s = sprintf('BT %.2F %.2F Td (%s) Tj ET', $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        if ($this->ColorFlag) {
            $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
        }
        $this->_out($s);
    }

    public function Ln($h = null)
    {
        $this->x = $this->lMargin;
        if ($h === null) {
            $this->y += $this->lasth;
        } else {
            $this->y += $h;
        }
    }

    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        $k = $this->k;
        if ($this->y + $h > $this->PageBreakTrigger && !$this->InFooter && $this->AutoPageBreak) {
            $x = $this->x;
            $ws = $this->ws;
            if ($ws > 0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation, $this->CurPageSize);
            $this->x = $x;
            if ($ws > 0) {
                $this->ws = $ws;
                $this->_out(sprintf('%.3F Tw', $ws * $k));
            }
        }
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $s = '';
        if ($fill || $border == 1) {
            $op = $fill ? ($border == 1 ? 'B' : 'f') : 'S';
            $s = sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x * $k, ($this->h - $this->y - $h) * $k, $w * $k, $h * $k, $op);
        }
        if (is_string($border)) {
            if (strpos($border, 'L') !== false) {
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $this->x * $k, ($this->h - $this->y) * $k, $this->x * $k, ($this->h - $this->y - $h) * $k);
            }
            if (strpos($border, 'T') !== false) {
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $this->x * $k, ($this->h - $this->y) * $k, ($this->x + $w) * $k, ($this->h - $this->y) * $k);
            }
            if (strpos($border, 'R') !== false) {
                $s += sprintf('%.2F %.2F m %.2F %.2F l S ', ($this->x + $w) * $k, ($this->h - $this->y) * $k, ($this->x + $w) * $k, ($this->h - $this->y - $h) * $k);
            }
            if (strpos($border, 'B') !== false) {
                $s += sprintf('%.2F %.2F m %.2F %.2F l S ', $this->x * $k, ($this->h - $this->y - $h) * $k, ($this->x + $w) * $k, ($this->h - $this->y - $h) * $k);
            }
        }
        if ($txt !== '') {
            $dx = 0;
            if ($align == 'R') {
                $dx = $w - $this->cMargin - $this->GetStringWidth($txt);
            } elseif ($align == 'C') {
                $dx = ($w - $this->GetStringWidth($txt)) / 2;
            } else {
                $dx = $this->cMargin;
            }
            if ($this->ColorFlag) {
                $s .= 'q ' . $this->TextColor . ' ';
            }
            $txt2 = str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
            $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET', ($this->x + $dx) * $k, ($this->h - $this->y - 0.5 * $h - 0.3 * $this->FontSize) * $k, $txt2);
            if ($this->ColorFlag) {
                $s .= ' Q';
            }
            if ($link) {
                $this->Link($this->x + $dx, $this->y + 0.5 * $h, $this->GetStringWidth($txt), $h, $link);
            }
        }
        if ($s) {
            $this->_out($s);
        }
        $this->lasth = $h;
        if ($ln > 0) {
            $this->Ln($h);
        } else {
            $this->x += $w;
        }
    }

    public function GetStringWidth($s)
    {
        $s = (string) $s;
        $cw = $this->CurrentFont['cw'];
        $w = 0;
        $l = strlen($s);
        for ($i = 0; $i < $l; ++$i) {
            $w += $cw[$s[$i]];
        }
        return $w * $this->FontSize / 1000;
    }

    protected function _escape($s)
    {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace('(', '\\(', $s);
        $s = str_replace(')', '\\)', $s);
        $s = str_replace("\r", '\\r', $s);
        return $s;
    }

    protected function _out($s)
    {
        if ($this->state == 2) {
            $this->pages[$this->page] .= $s . "\n";
        } else {
            $this->buffer .= $s . "\n";
        }
    }

    public function Error($msg)
    {
        throw new Exception('FPDF error: ' . $msg);
    }

    public function Output($dest = '', $name = '', $isUTF8 = false)
    {
        if ($this->state < 3) {
            $this->Close();
        }
        $dest = strtoupper($dest);
        if ($dest == '') {
            $dest = 'I';
        }
        if ($dest == 'I') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $name . '"');
            echo $this->buffer;
        } elseif ($dest == 'D') {
            header('Content-Type: application/x-download');
            header('Content-Disposition: attachment; filename="' . $name . '"');
            echo $this->buffer;
        } elseif ($dest == 'F') {
            $f = fopen($name, 'wb');
            if (!$f) {
                $this->Error('Unable to create output file: ' . $name);
            }
            fwrite($f, $this->buffer, strlen($this->buffer));
            fclose($f);
        } elseif ($dest == 'S') {
            return $this->buffer;
        } else {
            $this->Error('Incorrect output destination: ' . $dest);
        }
        return '';
    }

    public function Close()
    {
        if ($this->state == 3) {
            return;
        }
        if ($this->page == 0) {
            $this->AddPage();
        }
        $this->InFooter = true;
        $this->Footer();
        $this->InFooter = false;
        $this->_endpage();
        $this->_enddoc();
    }

    protected function _beginpage($orientation, $size)
    {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';
    }

    protected function _endpage()
    {
        $this->state = 1;
    }

    protected function _enddoc()
    {
        $this->state = 3;
        $this->_putpages();
        $this->_putresources();
        $this->_putcatalog();
        $this->_putinfo();
        $this->_putheader();
        $this->_puttrailer();
    }

    protected function _putpages()
    {
        $nb = $this->page;
        if (!empty($this->AliasNbPages)) {
            for ($n = 1; $n <= $nb; $n++) {
                $this->pages[$n] = str_replace($this->AliasNbPages, $nb, $this->pages[$n]);
            }
        }
        $this->_out('2 0 obj');
        $this->_out('<</Type /Pages');
        $kids = '/Kids [';
        for ($n = 1; $n <= $nb; $n++) {
            $kids .= (3 + $n) . ' 0 R ';
        }
        $this->_out($kids . ']');
        $this->_out('/Count ' . $nb);
        $this->_out('>>');
        $this->_out('endobj');
        for ($n = 1; $n <= $nb; $n++) {
            $this->_out((3 + $n) . ' 0 obj');
            $this->_out('<</Type /Page');
            $this->_out('/Parent 2 0 R');
            $this->_out('/MediaBox [0 0 ' . sprintf('%.2F %.2F', $this->DefPageSize[0], $this->DefPageSize[1]) . ']');
            $this->_out('/Contents ' . ($this->n + $n * 2 - 1) . ' 0 R');
            $this->_out('/Resources <</Font <<');
            foreach ($this->fonts as $font) {
                $this->_out('/F' . $font['i'] . ' ' . $font['n'] . ' 0 R');
            }
            $this->_out('>> >>');
            $this->_out('>>');
            $this->_out('endobj');
            $page = $this->pages[$n];
            $this->_out((3 + $nb + $n) . ' 0 obj');
            $this->_out('<</Length ' . strlen($page) . '>>');
            $this->_out('stream');
            $this->_out($page);
            $this->_out('endstream');
            $this->_out('endobj');
        }
    }

    protected function _putresources()
    {
        foreach ($this->fonts as $font) {
            $this->_newobj();
            $this->_out('<</Type /Font');
            $this->_out('/BaseFont /' . $font['name']);
            $this->_out('/Subtype /Type1');
            $this->_out('/Encoding /WinAnsiEncoding');
            $this->_out('>>');
            $this->_out('endobj');
            $font['n'] = $this->n;
        }
    }

    protected function _putinfo()
    {
        $this->_out('1 0 obj');
        $this->_out('<</Producer ' . $this->_textstring('FPDF ' . FPDF_VERSION) . ' /CreationDate ' . $this->_textstring('D:' . date('YmdHis')) . '>>');
        $this->_out('endobj');
    }

    protected function _putcatalog()
    {
        $this->_out('3 0 obj');
        $this->_out('<</Type /Catalog');
        $this->_out('/Pages 2 0 R');
        if ($this->ZoomMode == 'fullpage') {
            $this->_out('/OpenAction [3 0 R /Fit]');
        } elseif ($this->ZoomMode == 'fullwidth') {
            $this->_out('/OpenAction [3 0 R /FitH null]');
        } elseif ($this->ZoomMode == 'real') {
            $this->_out('/OpenAction [3 0 R /XYZ null null 1]');
        } elseif (!is_string($this->ZoomMode)) {
            $this->_out('/OpenAction [3 0 R /XYZ null null ' . ($this->ZoomMode / 100) . ']');
        }
        if ($this->LayoutMode == 'single') {
            $this->_out('/PageLayout /SinglePage');
        } elseif ($this->LayoutMode == 'continuous') {
            $this->_out('/PageLayout /OneColumn');
        } elseif ($this->LayoutMode == 'two') {
            $this->_out('/PageLayout /TwoColumnLeft');
        }
        $this->_out('>>');
        $this->_out('endobj');
    }

    protected function _putheader()
    {
        $this->_out('%PDF-' . $this->PDFVersion);
    }

    protected function _puttrailer()
    {
        $this->_out('xref');
        $this->_out('0 ' . ($this->n + 1));
        $this->_out('0000000000 65535 f ');
        for ($i = 1; $i <= $this->n; $i++) {
            $this->_out(sprintf('%010d 00000 n ', $this->offsets[$i]));
        }
        $this->_out('trailer');
        $this->_out('<</Size ' . ($this->n + 1) . ' /Root 3 0 R /Info 1 0 R>>');
        $this->_out('startxref');
        $this->_out(strlen($this->buffer));
        $this->_out('%%EOF');
    }

    protected function _newobj()
    {
        $this->n++;
        $this->offsets[$this->n] = strlen($this->buffer);
        $this->_out($this->n . ' 0 obj');
    }

    protected function _textstring($s)
    {
        return '(' . $this->_escape($s) . ')';
    }
}

