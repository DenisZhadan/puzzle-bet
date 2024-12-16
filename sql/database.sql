CREATE TYPE client_gender AS ENUM ('male', 'female', 'other');

CREATE TYPE client_status AS ENUM ('unconfirmed', 'active', 'inactive', 'blocked', 'deleted');

CREATE TYPE currency AS ENUM ('EUR', 'USD', 'RUB');

CREATE TYPE bet_result AS ENUM ('win1', 'draw', 'win2');

CREATE TABLE client
(
    id          SERIAL PRIMARY KEY,
    user_name   TEXT NOT NULL UNIQUE,
    pass_hash   TEXT NOT NULL,
    first_name  TEXT,
    last_name   TEXT,
    gender      client_gender,
    birth_date  DATE,
    street      TEXT,
    city        TEXT,
    state       TEXT,
    postal_code VARCHAR(20),
    country     TEXT,
    status      client_status DEFAULT 'unconfirmed'
);

CREATE TABLE client_phone
(
    id        SERIAL PRIMARY KEY,
    client_id INT REFERENCES client (id) ON DELETE CASCADE,
    phone     VARCHAR(20) NOT NULL
);

CREATE TABLE client_email
(
    id        SERIAL PRIMARY KEY,
    client_id INT REFERENCES client (id) ON DELETE CASCADE,
    email     TEXT NOT NULL
);


CREATE TABLE client_balance
(
    id        SERIAL PRIMARY KEY,
    client_id INT REFERENCES client (id) ON DELETE CASCADE,
    currency  currency NOT NULL,
    amount    DECIMAL(10, 2) DEFAULT 0,
    UNIQUE (client_id, currency)
);

CREATE TABLE match
(
    id    SERIAL PRIMARY KEY,
    team1 TEXT NOT NULL,
    team2 TEXT NOT NULL
);

CREATE TABLE bet_list
(
    id              SERIAL PRIMARY KEY,
    client_id       INT               NOT NULL,
    match_id        INT               NOT NULL,
    expected_result public.bet_result NOT NULL,
    actual_result   public.bet_result NULL,
    amount          DECIMAL(10, 2)    NOT NULL,
    currency        public.currency   NOT NULL,
    coefficient     DECIMAL(5, 2)     NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES client (id),
    FOREIGN KEY (match_id) REFERENCES match (id)
);

CREATE OR REPLACE FUNCTION update_bet_result(
    match_id INT,
    actual_result bet_result,
    OUT total_bets INT,
    OUT total_winnings TEXT,
    OUT total_losses TEXT
) AS
$$
DECLARE
    bet          RECORD;
    winnings_map JSONB := '{}'::jsonb;
    losses_map   JSONB := '{}'::jsonb;
BEGIN
    total_bets := 0;

    FOR bet IN
        SELECT bl.*
        FROM bet_list bl
        WHERE bl.match_id = update_bet_result.match_id
          AND bl.actual_result IS NULL
        LOOP
            UPDATE bet_list
            SET actual_result = update_bet_result.actual_result
            WHERE id = bet.id;

            IF bet.expected_result = actual_result THEN
                UPDATE client_balance
                SET amount = amount + (bet.amount * bet.coefficient)
                WHERE client_id = bet.client_id
                  AND currency = bet.currency;

                winnings_map := jsonb_set(winnings_map,
                                          ARRAY [bet.currency::TEXT],
                                          to_jsonb(COALESCE((winnings_map ->> bet.currency::TEXT)::NUMERIC, 0) +
                                                   (bet.amount * bet.coefficient)),
                                          true);
            ELSE
                losses_map := jsonb_set(losses_map,
                                        ARRAY [bet.currency::TEXT],
                                        to_jsonb(COALESCE((losses_map ->> bet.currency::TEXT)::NUMERIC, 0) +
                                                 bet.amount),
                                        true);
            END IF;

            total_bets := total_bets + 1;
        END LOOP;

    total_winnings := array_to_string(
            ARRAY(
                    SELECT format('%s %s', to_char(value::NUMERIC, 'FM999999999.00'), key)
                    FROM jsonb_each_text(winnings_map)
            ), ', '
                      );

    total_losses := array_to_string(
            ARRAY(
                    SELECT format('%s %s', to_char(value::NUMERIC, 'FM999999999.00'), key)
                    FROM jsonb_each_text(losses_map)
            ), ', '
                    );

    IF total_winnings = '' THEN
        total_winnings := '0';
    END IF;

    IF total_losses = '' THEN
        total_losses := '0';
    END IF;
END;
$$ LANGUAGE 'plpgsql';


INSERT INTO client (user_name, pass_hash, first_name, last_name, gender, birth_date, street, city, state, postal_code,
                    country, status)
VALUES ('user1', 'password1-hash', 'John', 'Doe', 'male', '1990-01-01', '123 Main St', 'Springfield', 'IL', '62701',
        'USA',
        'active'),
       ('user2', 'password2-hash', 'Jane', 'Smith', 'female', '1992-02-02', '456 Elm St', 'Metropolis', 'NY', '10001',
        'USA',
        'active');

INSERT INTO client_phone (client_id, phone)
VALUES (1, '1234567890'),
       (1, '0987654321'),
       (2, '1231231234');

INSERT INTO client_email (client_id, email)
VALUES (1, 'john@example.com'),
       (1, 'john.doe@example.com'),
       (2, 'jane@example.com');

INSERT INTO client_balance (client_id, currency, amount)
VALUES (1, 'EUR', 100.00),
       (1, 'USD', 200.00),
       (1, 'RUB', 300.00),
       (2, 'USD', 400.00),
       (2, 'RUB', 500.00);

INSERT INTO match (team1, team2)
VALUES ('Real Madrid', 'Barcelona'),
       ('Manchester United', 'Liverpool'),
       ('Bayern Munich', 'Borussia Dortmund'),
       ('Juventus', 'AC Milan'),
       ('Paris Saint-Germain', 'Olympique Lyonnais'),
       ('Chelsea', 'Arsenal'),
       ('Inter Milan', 'Napoli'),
       ('Atletico Madrid', 'Sevilla'),
       ('Tottenham Hotspur', 'Manchester City'),
       ('Ajax', 'PSV Eindhoven');
