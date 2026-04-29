let lines = `•  Franklin 
•  Alessio
•  Otis
•  Senn 
•  Leno
•  Arda
•  Scout
•  Murat Y
•  Murat C
•  Thibo
•  Tyrone`.split(/\r?\n|,/);

lines.forEach(line => {
    let cleaned = line.replace(/^[^a-zA-ZÀ-ÿ0-9]+/, '').trim();
    console.log(`"${cleaned}"`);
});
