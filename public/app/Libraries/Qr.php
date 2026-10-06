<?php
namespace App\Libraries;
class Qr {
    private function mul(int $x, int $y): int { $z=0; for ($i=7;$i>=0;$i--){ $z=($z<<1)^((($z>>7)&1)*0x11D); $z^=((($y>>$i)&1)*$x);} return $z&0xFF; }
    private function divisor(int $deg): array { $r=array_fill(0,$deg,0); $r[$deg-1]=1; $root=1; for($i=0;$i<$deg;$i++){ for($j=0;$j<$deg;$j++){ $r[$j]=$this->mul($r[$j],$root); if($j+1<$deg) $r[$j]^=$r[$j+1]; } $root=$this->mul($root,0x02);} return $r; }
    private function remainder(array $data, array $div): array { $r=array_fill(0,count($div),0); foreach($data as $b){ $f=$b^$r[0]; array_shift($r); $r[]=0; foreach($div as $i=>$d) $r[$i]^=$this->mul($d,$f);} return $r; }
    public function matrix(string $text): array {
        $caps=[1=>17,2=>32,3=>53,4=>78,5=>106]; $dcw=[1=>19,2=>34,3=>55,4=>80,5=>108]; $ecc=[1=>7,2=>10,3=>15,4=>20,5=>26];
        $len=strlen($text); $ver=5; foreach($caps as $v=>$c){ if($len<=$c){$ver=$v;break;} }
        if ($len>$caps[$ver]) { $text=substr($text,0,$caps[$ver]); $len=strlen($text); }
        $bits='0100'.str_pad(decbin($len),8,'0',STR_PAD_LEFT);
        for($i=0;$i<$len;$i++) $bits.=str_pad(decbin(ord($text[$i])),8,'0',STR_PAD_LEFT);
        $max=$dcw[$ver]*8; $bits.=str_repeat('0',min(4,$max-strlen($bits)));
        while(strlen($bits)%8!==0) $bits.='0';
        $pad=0; while(strlen($bits)<$max){ $bits.=str_pad(decbin($pad?0x11:0xEC),8,'0',STR_PAD_LEFT); $pad^=1; }
        $cw=[]; for($i=0;$i<strlen($bits);$i+=8) $cw[]=(int)bindec(substr($bits,$i,8));
        $all=array_merge($cw,$this->remainder($cw,$this->divisor($ecc[$ver])));
        $size=17+4*$ver; $m=array_fill(0,$size,array_fill(0,$size,0)); $fn=array_fill(0,$size,array_fill(0,$size,false));
        $finder=function($ox,$oy) use (&$m,&$fn,$size){ for($dy=-1;$dy<=7;$dy++) for($dx=-1;$dx<=7;$dx++){ $y=$oy+$dy;$x=$ox+$dx; if($x<0||$y<0||$x>=$size||$y>=$size) continue; $in=($dy>=0&&$dy<=6&&$dx>=0&&$dx<=6); $m[$y][$x]=$in?((($dy===0||$dy===6||$dx===0||$dx===6)||($dy>=2&&$dy<=4&&$dx>=2&&$dx<=4))?1:0):0; $fn[$y][$x]=true; } };
        $finder(0,0); $finder($size-7,0); $finder(0,$size-7);
        for($i=8;$i<$size-8;$i++){ $v=$i%2===0?1:0; $m[6][$i]=$v;$fn[6][$i]=true; $m[$i][6]=$v;$fn[$i][6]=true; }
        $align=[1=>[],2=>[6,18],3=>[6,22],4=>[6,26],5=>[6,30]][$ver];
        foreach($align as $cy) foreach($align as $cx){ if(($cy===6&&$cx===6)||($cy===6&&$cx===$size-7)||($cy===$size-7&&$cx===6)) continue; for($dy=-2;$dy<=2;$dy++) for($dx=-2;$dx<=2;$dx++){ $m[$cy+$dy][$cx+$dx]=max(abs($dx),abs($dy))!==1?1:0; $fn[$cy+$dy][$cx+$dx]=true; } }
        $fc=[]; for($i=0;$i<=5;$i++) $fc[]=[$i,8]; $fc[]=[7,8]; $fc[]=[8,8]; $fc[]=[8,7]; for($i=9;$i<15;$i++) $fc[]=[8,14-$i]; for($i=0;$i<8;$i++) $fc[]=[8,$size-1-$i]; for($i=8;$i<15;$i++) $fc[]=[$size-15+$i,8]; $fc[]=[$size-8,8];
        foreach($fc as $c){ $m[$c[0]][$c[1]]=0; $fn[$c[0]][$c[1]]=true; }
        $ab=''; foreach($all as $b) $ab.=str_pad(decbin($b),8,'0',STR_PAD_LEFT);
        $bi=0;
        for($right=$size-1;$right>=1;$right-=2){ if($right===6) $right=5;
            for($vert=0;$vert<$size;$vert++){ for($j=0;$j<2;$j++){ $x=$right-$j; $up=((($right+1)&2)===0); $y=$up?$size-1-$vert:$vert; if($fn[$y][$x]) continue; $m[$y][$x]=$bi<strlen($ab)?(int)$ab[$bi]:0; $bi++; } } }
        for($y=0;$y<$size;$y++) for($x=0;$x<$size;$x++) if(!$fn[$y][$x]&&(($x+$y)%2===0)) $m[$y][$x]^=1;
        $fb=0x77C4;
        for($i=0;$i<=5;$i++) $m[$i][8]=($fb>>$i)&1;
        $m[7][8]=($fb>>6)&1; $m[8][8]=($fb>>7)&1; $m[8][7]=($fb>>8)&1;
        for($i=9;$i<15;$i++) $m[8][14-$i]=($fb>>$i)&1;
        for($i=0;$i<8;$i++) $m[8][$size-1-$i]=($fb>>$i)&1;
        for($i=8;$i<15;$i++) $m[$size-15+$i][8]=($fb>>$i)&1;
        $m[$size-8][8]=1;
        return $m;
    }
    public function svg(string $data): string {
        $m=$this->matrix($data); $n=count($m); $q=4; $s=$n+2*$q;
        $svg='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$s.' '.$s.'" shape-rendering="crispEdges"><rect width="'.$s.'" height="'.$s.'" fill="#fff"/>';
        for($y=0;$y<$n;$y++) for($x=0;$x<$n;$x++) if($m[$y][$x]) $svg.='<rect x="'.($x+$q).'" y="'.($y+$q).'" width="1" height="1" fill="#000"/>';
        return $svg.'</svg>';
    }
}