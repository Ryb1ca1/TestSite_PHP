<?php

function getFullnameFromParts($surname, $name, $patronymic) {
    return "$surname $name $patronymic";
}

function getPartsFromFullname($fullname) {
    $parts = explode(' ', $fullname, 3);
    return [
        'surname' => $parts[0],
        'name' => $parts[1],
        'patronymic' => $parts[2]
    ];
}

function getShortName($fullname) {
    $parts = getPartsFromFullname($fullname);
    return "{$parts['name']} " . mb_substr($parts['surname'], 0, 1) . ".";
}

function getGenderFromName($fullname) {
    $parts = getPartsFromFullname($fullname);
    $genderScore = 0;

    // Проверка признаков женского пола
    if (mb_substr($parts['patronymic'], -3) === 'вна') {
        $genderScore--;
    }
    if (mb_substr($parts['name'], -1) === 'а') {
        $genderScore--;
    }
    if (mb_substr($parts['surname'], -2) === 'ва') {
        $genderScore--;
    }

    // Проверка признаков мужского пола
    if (mb_substr($parts['patronymic'], -3) === 'ич') {
        $genderScore++;
    }
    if (mb_substr($parts['name'], -1) === 'й' || mb_substr($parts['name'], -1) === 'н') {
        $genderScore++;
    }
    if (mb_substr($parts['surname'], -1) === 'в') {
        $genderScore++;
    }

    if ($genderScore > 0) {
        return 1; // мужской пол
    } elseif ($genderScore < 0) {
        return -1; // женский пол
    } else {
        return 0; // неопределенный пол
    }
}

function getGenderDescription($audience) {
    $total = count($audience);
    $maleCount = 0;
    $femaleCount = 0;
    $unknownCount = 0;

    foreach ($audience as $person) {
        $gender = getGenderFromName($person['fullname']);
        if ($gender === 1) {
            $maleCount++;
        } elseif ($gender === -1) {
            $femaleCount++;
        } else {
            $unknownCount++;
        }
    }

    $malePercentage = round(($maleCount / $total) * 100, 1);
    $femalePercentage = round(($femaleCount / $total) * 100, 1);
    $unknownPercentage = round(($unknownCount / $total) * 100, 1);

    $output = "Гендерный состав аудитории:\n";
    $output .= "---------------------------\n";
    $output .= "Мужчины - $malePercentage%\n";
    $output .= "Женщины - $femalePercentage%\n";
    $output .= "Не удалось определить - $unknownPercentage%\n";

    return $output;
}

function getPerfectPartner($surname, $name, $patronymic, $audience) {
    $fullname = getFullnameFromParts($surname, $name, $patronymic);
    $gender = getGenderFromName($fullname);

    $oppositeGender = $gender === 1 ? -1 : 1;

    $compatiblePersons = array_filter($audience, function($person) use ($oppositeGender) {
        return getGenderFromName($person['fullname']) === $oppositeGender;
    });

    $totalCompatiblePersons = count($compatiblePersons);
    if ($totalCompatiblePersons === 0) {
        return "Извините, идеального партнера не найдено.";
    }

    $randomCompatiblePerson = $compatiblePersons[array_rand($compatiblePersons)];
    $randomFullName = $randomCompatiblePerson['fullname'];
    $randomPercentage = rand(5000, 10000) / 100;

    return "$fullname + $randomFullName =\n♡ Идеально на $randomPercentage% ♡";
}

$example_persons_array = [
    [
        'fullname' => 'Иванов Иван Иванович',
        'job' => 'tester',
    ],
    [
        'fullname' => 'Степанова Наталья Степановна',
        'job' => 'frontend-developer',
    ],
    [
        'fullname' => 'Пащенко Владимир Александрович',
        'job' => 'analyst',
    ],
    [
        'fullname' => 'Громов Александр Иванович',
        'job' => 'fullstack-developer',
    ],
    [
        'fullname' => 'Славин Семён Сергеевич',
        'job' => 'analyst',
    ],
    [
        'fullname' => 'Цой Владимир Антонович',
        'job' => 'frontend-developer',
    ],
    [
        'fullname' => 'Быстрая Юлия Сергеевна',
        'job' => 'PR-manager',
    ],
    [
        'fullname' => 'Шматко Антонина Сергеевна',
        'job' => 'HR-manager',
    ],
    [
        'fullname' => 'аль-Хорезми Мухаммад ибн-Муса',
        'job' => 'analyst',
    ],
    [
        'fullname' => 'Бардо Жаклин Фёдоровна',
        'job' => 'android-developer',
    ],
    [
        'fullname' => 'Шварцнегер Арнольд Густавович',
        'job' => 'babysitter',
    ],
];

foreach ($example_persons_array as $person) {
    $fullname = $person['fullname'];
    $fullname_parts = getPartsFromFullname($fullname);
    $shortName = getShortName($fullname);
    $reconstructedFullname = getFullnameFromParts($fullname_parts['surname'], $fullname_parts['name'], $fullname_parts['patronymic']);
    $gender = getGenderFromName($fullname);
    echo "Пол для $fullname: ";
    echo $gender === 1 ? "мужской" : ($gender === -1 ? "женский" : "неопределенный");
    echo ", Должность: {$person['job']}\n";
    echo "Полное разбиение: Фамилия: {$fullname_parts['surname']}, Имя: {$fullname_parts['name']}, Отчество: {$fullname_parts['patronymic']}, Должность: {$person['job']}\n";
    echo "Сокращенное имя: $shortName, Должность: {$person['job']}\n";
    echo "Перестроенное ФИО: $reconstructedFullname\n";
}

echo getGenderDescription($example_persons_array);
$surname = 'Иванов';
$name = 'Иван';
$patronymic = 'Иванович';
echo getPerfectPartner($surname, $name, $patronymic, $example_persons_array);
