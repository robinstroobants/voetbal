let poolItems = [
    { sidx: 1, name: 'Alice', histRatio: 0.8 },
    { sidx: 2, name: 'Bob', histRatio: 0.5 },
    { sidx: 3, name: 'Charlie', histRatio: 0.9 },
    { sidx: 4, name: 'Dave', histRatio: 0.5 }
];

let globalPlayerStats = {
    1: { name: 'Alice', fieldMin: 15, matchAvailable: 30 },
    2: { name: 'Bob', fieldMin: 15, matchAvailable: 30 },
    3: { name: 'Charlie', fieldMin: 0, matchAvailable: 30 },
    4: { name: 'Dave', fieldMin: 30, matchAvailable: 30 }
};

let sortFunc = (sidxA, sidxB) => {
    let pA = globalPlayerStats[sidxA];
    let pB = globalPlayerStats[sidxB];
    let ratioA = pA.fieldMin / pA.matchAvailable;
    let ratioB = pB.fieldMin / pB.matchAvailable;
    if (Math.abs(ratioA - ratioB) > 0.001) return ratioA - ratioB;
    
    let histA = poolItems.find(x => x.sidx === sidxA).histRatio;
    let histB = poolItems.find(x => x.sidx === sidxB).histRatio;
    if (Math.abs(histA - histB) > 0.001) return histA - histB;
    
    let nameA = pA.name.toLowerCase();
    let nameB = pB.name.toLowerCase();
    if (nameA < nameB) return -1;
    if (nameA > nameB) return 1;
    return 0;
};

poolItems.sort((a, b) => sortFunc(a.sidx, b.sidx));
console.log(poolItems.map(x => x.name));
