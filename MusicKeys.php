<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 25/02/15
 * Time: 02:11
 */

class MusicKeys {
    public static $keys = array(  0 => 'Do', 1 => 'Do♯',
                            2 => 'Ré', 3 => 'Mi♭',
                            4 => 'Mi', 5 => 'Fa',
                            6 => 'Fa♯', 7 => 'Sol',
                            8 => 'Sol♯', 9 => 'La',
                            10 => 'Si♭', 11 => 'Si',
                            null => 'Atonal');

    public static $modes = array(0 => 'Mineur', 1 => 'Majeur', null => '');

    public static $pitch_desc = array(
        0 => array(
            0 => 'Surtout agréable, charmant, mais aussi triste, désolé.
                  Porte facilement à la somnolence. Deuil ou sensation
                  caressante.',
            1 => 'Accents de pénitence, entretiens intimes avec Dieu, avec l\'ami et la compagne de vie, soupirs de l\'amitié et de l\'amour insatisfaits relèvent de son domaine',
            2 => 'Dévot, calme, grand, agréable, content. Event.
                  Divertissant, non pas sautillant mais fluide. Tonalité des
                  choses d’église et dans la vie commune, de la
                  tranquillité de l’âme.',
            3 => 'Très pathétique. Jamais grave ou plaintif ou exubérant. Egalement de la conversation intime avec Dieu.
                  Expression de la trinité avec ses trois bémols.',
            4 => 'Effemmé, amoureux et plaintif. Pensée profonde. Trouble et tristesse, mais de telle
                  manière qu’on espère la consolation : quelque chose d’allègre, mais non pas gai.',
            5 => 'Résigné, modéré, il exprime l’angoisse du cœur, profonde, lourde. C’est le ton du doute. Il produit une mélancolie noire et désespérée qui plonge ses auditeurs dans la grisaille au point de leur donner le frisson.',
            6 => 'Grand trouble, plutôt languissant et amoureux. Quelque chose d’abandonné, de solitaire, de misanthrope.
                  Ton obscur. Tiraille la passion comme le chien hargneux la draperie.',
            7 => 'Sérieux et magnifique. C’est presque le plus beau de tous les tons : il mêle au
                  sérieux du sol majeur une tendresse pleine d\'agréments
                  accompagnée d\'élégance et de goût. Cela lui donne une aisance, une souplesse infinie pour traduire à la fois l\'affection et l\'apaisement, le désir ardent et l\'enjouement, tout comme les
                  plaintes modérées ou la joie tempérée. Sol mineur est extrêmement flexible.',
            8 => 'Morose, grognon, cœur oppressé jusqu’à l’étouffement.',
            9 => 'Allure fastueuse et grave. Mais aussi dirigé vers la flatterie. Par nature, bien modéré, un peu plaintif,
                  décent (respectable), tranquille, invitant même au sommeil. Peut être employé pour tous les mouvements de l’âme, et le public apprécie son côté modéré et sa douceur',
            10 => 'Obscur et terrible. Un original bourru qui prend rarement une mine complaisante et se moque de Dieu et du monde. Prépare au suicide.',
            11 => 'Solitaire et mélancolique. Patience, attente tranquille de son sort, et de la résignation à la
                   volonté de Dieu. Sa plainte est si douce qu’elle n’éclate jamais en murmures ou en vagissements outrageants.',
            null => 'Pas de description'),

        1 => array(
            0 => 'Parfaitement pur. Innocence, naïveté, Eventuellement
                  charmant ou tendre langage d’enfants.',
            1 => 'Gros fan de Djent omg.',
            2 => 'Piquant, brillant, vif, opiniâtre, obstiné, bruyant,
                  amusant, guerrier, stimulant. Event. délicat. Trompettes
                  et timbales. Ton des triomphes, des Alleluias, des cris de guerre et de joie
                  de la victoire. ',
            3 => 'Horrible, affreux. Sensation d’anxiété, de trouble de l’âme, de désespoir.',
            4 => 'Allégresse bruyante, sans un sentiment de jouissance complètement abouti',
            5 => 'Magnanimité, fermeté, persévérance, amour, vertu,
                  facilité. On ne peut mieux décrire la sagesse, la
                  gentillesse de cette tonalité qu’en la comparant à un
                  homme beau, qui réussit tout ce qu’il entreprend aussi
                  vite qu’il veut et qui a bonne grâce',
            6 => 'Triomphe dans l’adversité. On respire librement sur le sommet de la colline.',
            7 => 'Champêtre, idyllique. Reconnaissance affectueuse pour amitié
                  sincère et amour fidèle. Convient aussi bien aux choses sérieuses qu’aux gaies.',
            8 => 'Ton du fossoyeur, mort, décomposition, jugement, éternité.',
            9 => 'Ce ton est saisissant. Il brille immédiatement, et plus pour
                  les passions plaintives et tristes que pour le divertissement. Il contient des déclarations d’amour innocent, pleines d\'espoir,
                  et convient particulièrement au violon. Revoir l’être aimé, gaîté juvénile, ...',
            10 => 'Divertissant,fastueux, mais aussi modeste. Ce ton peut passer à la fois pour magnifique et mignon. Il caractérise l\'amour enjoué, la bonne conscience, l\'espoir, le regard vers un monde meilleur.
            Parmi les autres qualités qu’on lui attribue on ne saurait lui daigner qu’il emmène l’âme vers les sommets',
            11 => 'Caractère contrariant, dur et désagréable, et en plus, quelque chose de désespéré. Il est peu employé.',
            null => 'Pas de description'),
        null => ''
    );

    public static function getPitchDesc($pitch) {
        $str = explode(' ', $pitch);
        $key = array_search($str[0], MusicKeys::$keys);
        $mode = array_search($str[1], MusicKeys::$modes);

        return MusicKeys::$pitch_desc[$mode][$key];
    }

    public static function getPitch($key, $mode) {
        if($key == 8 && $mode == 1)
            return 'La♭ Majeur';

        $key =  MusicKeys::$keys[$key];
        $mode = MusicKeys::$modes[$mode];

        return $key . ' ' . $mode;
    }

} 