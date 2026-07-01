/**
 * Green Torque — vehicle makes and models (booking form + SEO)
 */
window.VEHICLE_DATA = {
  Acura: ['ILX', 'Integra', 'MDX', 'RDX', 'TLX', 'Other'],
  Audi: ['A3', 'A4', 'A6', 'Q3', 'Q5', 'Q7', 'e-tron', 'Other'],
  BMW: ['2 Series', '3 Series', '5 Series', 'X1', 'X3', 'X5', 'i4', 'iX', 'Other'],
  Chevrolet: ['Blazer', 'Bolt EV', 'Camaro', 'Equinox', 'Malibu', 'Silverado', 'Suburban', 'Tahoe', 'Traverse', 'Other'],
  Dodge: ['Challenger', 'Charger', 'Durango', 'Other'],
  Ford: ['Bronco', 'Edge', 'Escape', 'Explorer', 'F-150', 'Focus', 'Fusion', 'Mach-E', 'Mustang', 'Ranger', 'Other'],
  GMC: ['Acadia', 'Canyon', 'Sierra', 'Terrain', 'Yukon', 'Other'],
  Honda: ['Accord', 'Civic', 'CR-V', 'Fit', 'HR-V', 'Odyssey', 'Pilot', 'Prologue', 'Ridgeline', 'Other'],
  Hyundai: ['Elantra', 'Ioniq 5', 'Ioniq 6', 'Kona', 'Palisade', 'Santa Fe', 'Sonata', 'Tucson', 'Other'],
  Jeep: ['Cherokee', 'Compass', 'Grand Cherokee', 'Wrangler', 'Other'],
  Kia: ['EV6', 'Forte', 'K5', 'Niro', 'Seltos', 'Sorento', 'Soul', 'Sportage', 'Telluride', 'Other'],
  Lexus: ['ES', 'GX', 'IS', 'NX', 'RX', 'UX', 'Other'],
  Mazda: ['CX-30', 'CX-5', 'CX-50', 'CX-90', 'Mazda3', 'Mazda6', 'Other'],
  'Mercedes-Benz': ['C-Class', 'E-Class', 'EQE', 'EQS', 'GLC', 'GLE', 'Other'],
  Nissan: ['Altima', 'Armada', 'Frontier', 'Leaf', 'Maxima', 'Murano', 'Pathfinder', 'Rogue', 'Sentra', 'Titan', 'Other'],
  Ram: ['1500', '2500', '3500', 'ProMaster', 'Other'],
  Subaru: ['Ascent', 'Crosstrek', 'Forester', 'Impreza', 'Legacy', 'Outback', 'Other'],
  Tesla: ['Model 3', 'Model S', 'Model X', 'Model Y', 'Cybertruck', 'Other'],
  Toyota: ['4Runner', 'bZ4X', 'Camry', 'Corolla', 'Highlander', 'Prius', 'RAV4', 'Sienna', 'Tacoma', 'Tundra', 'Other'],
  Volkswagen: ['Atlas', 'Golf', 'ID.4', 'Jetta', 'Passat', 'Tiguan', 'Other'],
  Volvo: ['EX30', 'S60', 'XC40', 'XC60', 'XC90', 'Other'],
  Other: ['Other']
};

window.VEHICLE_MAKES = Object.keys(window.VEHICLE_DATA).filter(function (make) {
  return make !== 'Other';
});
