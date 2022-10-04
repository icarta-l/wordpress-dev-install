<?php
/**
 * Randomly generates a strong password
 * 
 */
final class GeneratePassword
{
    const UPPER_CASED_LETTERS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    const LOWER_CASED_LETTERS = "abcdefghijklmnopqrstuvwxyz";
    const SYMBOLS_TO_USE = "~`!@#$%^&*()_-+={[}]|\:;\"'<,>.?/";

    private int $number_of_upper_cased_letters = 0;
    private int $number_of_lower_cased_letters = 0;
    private int $number_of_symbols = 0;
    private int $number_of_numbers = 0;

    public function generateStrongPassword (int $max_length = 35) : string
    {
        $password_length = random_int(25, $max_length);
        $password = "";
        while (strlen($password) < $password_length) {
            $password = $this->getRandomTypeOfCharacter($password);
        }
        return $password;
    }

    /**
     * Randomly add a character to $password
     * 
     */

    private function getRandomTypeOfCharacter(string $password) : string 
    {
        switch ($this->handleCharacterChoice()) {
            case "upper_cased_letters":
            $password = $this->addAnUpperCasedLetter($password);
            break;

            case "lower_cased_letters":
            $password = $this->addALowerCasedLetter($password);
            break;

            case "symbols":
            $password = $this->addASymbol($password);
            break;

            case "numbers":
            $password = $this->addANumber($password);
            break;
        }

        return $password;
    }

    private function handleCharacterChoice() : string
    {
        $choices = [
            "upper_cased_letters",
            "lower_cased_letters",
            "symbols",
            "numbers"
        ];

        $result = $this->checkNumberOfCharacterType($choices);

        if (is_null($result)) {
            $result = $choices;
        }

        $choice = $result[random_int(0, count($result) - 1)];

        return $choice;
    }

    private function checkNumberOfCharacterType(array $choices) : ?array
    {
        if ($this->number_of_upper_cased_letters >= 3) {
            array_splice($choices, array_search("upper_cased_letters", $choices), 1);
        }

        if ($this->number_of_lower_cased_letters >= 3) {
            array_splice($choices, array_search("lower_cased_letters", $choices), 1);
        }

        if ($this->number_of_symbols >= 3) {
            array_splice($choices, array_search("symbols", $choices), 1);
        }

        if ($this->number_of_numbers >= 3) {
            array_splice($choices, array_search("numbers", $choices), 1);
        }

        if (count($choices) === 0) {
            return null;
        } else {
            return $choices;
        }
    }

    /**
     * Add a character type
     * 
     */

    private function addAnUpperCasedLetter(string $password) : string
    {
        $password .= substr( self::UPPER_CASED_LETTERS, random_int(0, strlen(self::UPPER_CASED_LETTERS) - 1), 1 );
        $this->number_of_upper_cased_letters++;
        return $password;
    }

    private function addALowerCasedLetter(string $password) : string
    {
        $password .= substr( self::LOWER_CASED_LETTERS, random_int(0, strlen(self::LOWER_CASED_LETTERS) - 1), 1 );
        $this->number_of_lower_cased_letters++;
        return $password;
    }

    private function addASymbol(string $password) : string
    {
        $password .= substr( self::SYMBOLS_TO_USE, random_int(0, strlen(self::SYMBOLS_TO_USE) - 1), 1 );
        $this->number_of_symbols++;
        return $password;
    }

    private function addANumber(string $password) : string
    {
        $password .= random_int(0, 9);
        $this->number_of_numbers++;
        return $password;
    }
}